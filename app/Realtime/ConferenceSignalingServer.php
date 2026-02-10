<?php

namespace App\Realtime;

use App\Support\ConferenceSignalingToken;
use Workerman\Connection\ConnectionInterface;
use Workerman\Worker;

class ConferenceSignalingServer
{
    /**
     * @var array<int, array<string, mixed>>
     */
    private array $connections = [];

    /**
     * @var array<string, array<string, ConnectionInterface>>
     */
    private array $rooms = [];

    public function buildWorker(string $host, int $port): Worker
    {
        $worker = new Worker("websocket://{$host}:{$port}");

        $worker->count = 1;
        $worker->name = 'conference-signaling';

        $worker->onConnect = function (ConnectionInterface $connection): void {
            $this->connections[$connection->id] = [
                'joined' => false,
                'roomId' => null,
                'actorId' => null,
                'name' => null,
                'role' => null,
            ];
        };

        $worker->onMessage = function (ConnectionInterface $connection, string $raw): void {
            $this->onMessage($connection, $raw);
        };

        $worker->onClose = function (ConnectionInterface $connection): void {
            $this->onClose($connection);
        };

        return $worker;
    }

    private function onMessage(ConnectionInterface $connection, string $raw): void
    {
        $message = json_decode($raw, true);

        if (! is_array($message)) {
            $this->sendError($connection, 'invalid-json', 'Message must be valid JSON.');

            return;
        }

        $type = (string) ($message['type'] ?? '');

        if ($type === 'join') {
            $this->handleJoin($connection, $message);

            return;
        }

        $state = $this->connections[$connection->id] ?? null;

        if (! is_array($state) || ! ($state['joined'] ?? false)) {
            $this->sendError($connection, 'not-joined', 'Join the room before sending this message.');

            return;
        }

        match ($type) {
            'offer', 'answer', 'ice-candidate' => $this->forwardToPeer($connection, $message, $type),
            'chat' => $this->broadcastChat($connection, $message),
            'meeting-ended' => $this->broadcastMeetingEnded($connection),
            'raise-hand' => $this->broadcastRaiseHand($connection, $message),
            'emoji-reaction' => $this->broadcastEmojiReaction($connection, $message),
            'mute-participant' => $this->handleMuteParticipant($connection, $message),
            'unmute-participant' => $this->handleUnmuteParticipant($connection, $message),
            'disable-cam-participant' => $this->handleDisableCamParticipant($connection, $message),
            'enable-cam-participant' => $this->handleEnableCamParticipant($connection, $message),
            'screen-share-started' => $this->broadcastScreenShareEvent($connection, 'screen-share-started'),
            'screen-share-stopped' => $this->broadcastScreenShareEvent($connection, 'screen-share-stopped'),
            default => $this->sendError($connection, 'unsupported-type', "Unsupported message type [{$type}]."),
        };
    }

    /**
     * @param  array<string, mixed>  $message
     */
    private function handleJoin(ConnectionInterface $connection, array $message): void
    {
        $token = trim((string) ($message['token'] ?? ''));
        $requestedRoomId = trim((string) ($message['roomId'] ?? ''));

        if ($token === '' || $requestedRoomId === '') {
            $this->sendError($connection, 'invalid-join', 'Join requires token and roomId.');
            $connection->close();

            return;
        }

        $claims = ConferenceSignalingToken::verify($token);

        if ($claims === null) {
            $this->sendError($connection, 'unauthorized', 'Invalid or expired signaling token.');
            $connection->close();

            return;
        }

        $conferenceId = (int) ($claims['conferenceId'] ?? 0);
        $actorId = trim((string) ($claims['actorId'] ?? ''));
        $name = trim((string) ($claims['actorName'] ?? ''));
        $role = trim((string) ($claims['actorRole'] ?? 'participant'));
        $tokenRoomId = $conferenceId > 0 ? "conference-{$conferenceId}" : '';

        if (
            $conferenceId <= 0
            || $actorId === ''
            || $tokenRoomId === ''
            || $tokenRoomId !== $requestedRoomId
        ) {
            $this->sendError($connection, 'invalid-join', 'Token does not match room membership.');
            $connection->close();

            return;
        }

        if (isset($this->rooms[$requestedRoomId][$actorId])) {
            $previousConnection = $this->rooms[$requestedRoomId][$actorId];
            if ($previousConnection !== $connection) {
                $this->send($previousConnection, [
                    'type' => 'system',
                    'event' => 'connection-replaced',
                ]);
                $previousConnection->close();
            }
        }

        $this->connections[$connection->id] = [
            'joined' => true,
            'roomId' => $requestedRoomId,
            'actorId' => $actorId,
            'name' => $name !== '' ? $name : $actorId,
            'role' => $role !== '' ? $role : 'participant',
        ];

        $this->rooms[$requestedRoomId] ??= [];
        $this->rooms[$requestedRoomId][$actorId] = $connection;

        $self = $this->participantFromState($this->connections[$connection->id]);
        $participants = [];

        foreach ($this->rooms[$requestedRoomId] as $peerConnection) {
            $peerState = $this->connections[$peerConnection->id] ?? null;
            if (is_array($peerState) && ($peerState['joined'] ?? false)) {
                $participants[] = $this->participantFromState($peerState);
            }
        }

        $this->send($connection, [
            'type' => 'joined',
            'roomId' => $requestedRoomId,
            'self' => $self,
            'participants' => $participants,
        ]);

        $this->broadcastToRoom(
            $requestedRoomId,
            [
                'type' => 'peer-joined',
                'roomId' => $requestedRoomId,
                'participant' => $self,
            ],
            $connection
        );
    }

    /**
     * @param  array<string, mixed>  $message
     */
    private function forwardToPeer(ConnectionInterface $connection, array $message, string $type): void
    {
        $state = $this->connections[$connection->id] ?? null;
        if (! is_array($state)) {
            return;
        }

        $roomId = (string) ($state['roomId'] ?? '');
        $to = trim((string) ($message['to'] ?? ''));
        $payload = $message['payload'] ?? null;

        if ($roomId === '' || $to === '' || ! is_array($this->rooms[$roomId] ?? null) || ! isset($this->rooms[$roomId][$to])) {
            $this->sendError($connection, 'peer-not-found', "Target peer [{$to}] is not in this room.");

            return;
        }

        if (! is_array($payload)) {
            $this->sendError($connection, 'invalid-payload', "Message type [{$type}] requires an object payload.");

            return;
        }

        $targetConnection = $this->rooms[$roomId][$to];
        $from = $this->participantFromState($state);

        $this->send($targetConnection, [
            'type' => $type,
            'roomId' => $roomId,
            'from' => $from,
            'payload' => $payload,
        ]);
    }

    /**
     * @param  array<string, mixed>  $message
     */
    private function broadcastChat(ConnectionInterface $connection, array $message): void
    {
        $state = $this->connections[$connection->id] ?? null;
        if (! is_array($state)) {
            return;
        }

        $roomId = (string) ($state['roomId'] ?? '');
        $chatMessage = trim((string) ($message['message'] ?? ''));

        if ($roomId === '' || $chatMessage === '') {
            return;
        }

        $chatMessage = mb_substr($chatMessage, 0, 300);

        $this->broadcastToRoom($roomId, [
            'type' => 'chat',
            'roomId' => $roomId,
            'from' => $this->participantFromState($state),
            'message' => $chatMessage,
        ]);
    }

    private function broadcastMeetingEnded(ConnectionInterface $connection): void
    {
        $state = $this->connections[$connection->id] ?? null;
        if (! is_array($state)) {
            return;
        }

        if (($state['role'] ?? '') !== 'teacher') {
            $this->sendError($connection, 'forbidden', 'Only the teacher can end the meeting.');

            return;
        }

        $roomId = (string) ($state['roomId'] ?? '');
        if ($roomId === '') {
            return;
        }

        $this->broadcastToRoom(
            $roomId,
            [
                'type' => 'meeting-ended',
                'roomId' => $roomId,
                'from' => $this->participantFromState($state),
            ],
            $connection
        );
    }

    /**
     * @param  array<string, mixed>  $message
     */
    private function broadcastRaiseHand(ConnectionInterface $connection, array $message): void
    {
        $state = $this->connections[$connection->id] ?? null;
        if (! is_array($state)) {
            return;
        }

        $roomId = (string) ($state['roomId'] ?? '');
        if ($roomId === '') {
            return;
        }

        $raised = (bool) ($message['raised'] ?? true);

        $this->broadcastToRoom($roomId, [
            'type' => 'raise-hand',
            'roomId' => $roomId,
            'from' => $this->participantFromState($state),
            'raised' => $raised,
        ]);
    }

    /**
     * @param  array<string, mixed>  $message
     */
    private function broadcastEmojiReaction(ConnectionInterface $connection, array $message): void
    {
        $state = $this->connections[$connection->id] ?? null;
        if (! is_array($state)) {
            return;
        }

        $roomId = (string) ($state['roomId'] ?? '');
        if ($roomId === '') {
            return;
        }

        $emoji = trim((string) ($message['emoji'] ?? ''));
        if ($emoji === '') {
            return;
        }

        // Limit emoji to a reasonable length (single emoji)
        $emoji = mb_substr($emoji, 0, 4);

        $this->broadcastToRoom($roomId, [
            'type' => 'emoji-reaction',
            'roomId' => $roomId,
            'from' => $this->participantFromState($state),
            'emoji' => $emoji,
        ]);
    }

    /**
     * Teacher-only: instruct a participant to mute their microphone.
     *
     * @param  array<string, mixed>  $message
     */
    private function handleMuteParticipant(ConnectionInterface $connection, array $message): void
    {
        $this->sendTeacherCommand($connection, $message, 'force-mute');
    }

    /**
     * Teacher-only: instruct a participant to unmute their microphone.
     *
     * @param  array<string, mixed>  $message
     */
    private function handleUnmuteParticipant(ConnectionInterface $connection, array $message): void
    {
        $this->sendTeacherCommand($connection, $message, 'force-unmute');
    }

    /**
     * Teacher-only: instruct a participant to disable their camera.
     *
     * @param  array<string, mixed>  $message
     */
    private function handleDisableCamParticipant(ConnectionInterface $connection, array $message): void
    {
        $this->sendTeacherCommand($connection, $message, 'force-cam-off');
    }

    /**
     * Teacher-only: instruct a participant to enable their camera.
     *
     * @param  array<string, mixed>  $message
     */
    private function handleEnableCamParticipant(ConnectionInterface $connection, array $message): void
    {
        $this->sendTeacherCommand($connection, $message, 'force-cam-on');
    }

    /**
     * Common helper to send teacher-initiated commands to a specific participant.
     *
     * @param  array<string, mixed>  $message
     */
    private function sendTeacherCommand(ConnectionInterface $connection, array $message, string $commandType): void
    {
        $state = $this->connections[$connection->id] ?? null;
        if (! is_array($state)) {
            return;
        }

        if (($state['role'] ?? '') !== 'teacher') {
            $this->sendError($connection, 'forbidden', 'Only the teacher can use moderator controls.');

            return;
        }

        $roomId = (string) ($state['roomId'] ?? '');
        $targetId = trim((string) ($message['targetId'] ?? ''));

        if ($roomId === '' || $targetId === '' || ! isset($this->rooms[$roomId][$targetId])) {
            $this->sendError($connection, 'peer-not-found', "Target participant [{$targetId}] is not in this room.");

            return;
        }

        $targetConnection = $this->rooms[$roomId][$targetId];

        $this->send($targetConnection, [
            'type' => $commandType,
            'roomId' => $roomId,
            'from' => $this->participantFromState($state),
        ]);

        // Also broadcast to the room so the teacher UI updates
        $this->broadcastToRoom($roomId, [
            'type' => 'participant-control',
            'roomId' => $roomId,
            'action' => $commandType,
            'targetId' => $targetId,
            'from' => $this->participantFromState($state),
        ]);
    }

    /**
     * Broadcast screen share start/stop events.
     */
    private function broadcastScreenShareEvent(ConnectionInterface $connection, string $eventType): void
    {
        $state = $this->connections[$connection->id] ?? null;
        if (! is_array($state)) {
            return;
        }

        $roomId = (string) ($state['roomId'] ?? '');
        if ($roomId === '') {
            return;
        }

        $this->broadcastToRoom($roomId, [
            'type' => $eventType,
            'roomId' => $roomId,
            'from' => $this->participantFromState($state),
        ]);
    }

    private function onClose(ConnectionInterface $connection): void
    {
        $state = $this->connections[$connection->id] ?? null;
        unset($this->connections[$connection->id]);

        if (! is_array($state) || ! ($state['joined'] ?? false)) {
            return;
        }

        $roomId = (string) ($state['roomId'] ?? '');
        $actorId = (string) ($state['actorId'] ?? '');

        if ($roomId === '' || $actorId === '') {
            return;
        }

        if (isset($this->rooms[$roomId][$actorId]) && $this->rooms[$roomId][$actorId] === $connection) {
            unset($this->rooms[$roomId][$actorId]);
        }

        if (empty($this->rooms[$roomId])) {
            unset($this->rooms[$roomId]);
        } else {
            $this->broadcastToRoom($roomId, [
                'type' => 'peer-left',
                'roomId' => $roomId,
                'participant' => $this->participantFromState($state),
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $state
     * @return array{id: string, name: string, role: string}
     */
    private function participantFromState(array $state): array
    {
        return [
            'id' => (string) ($state['actorId'] ?? ''),
            'name' => (string) ($state['name'] ?? ''),
            'role' => (string) ($state['role'] ?? 'participant'),
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function send(ConnectionInterface $connection, array $payload): void
    {
        $connection->send((string) json_encode($payload, JSON_UNESCAPED_SLASHES));
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function broadcastToRoom(string $roomId, array $payload, ?ConnectionInterface $except = null): void
    {
        foreach ($this->rooms[$roomId] ?? [] as $peerConnection) {
            if ($except !== null && $peerConnection === $except) {
                continue;
            }

            $this->send($peerConnection, $payload);
        }
    }

    private function sendError(ConnectionInterface $connection, string $code, string $message): void
    {
        $this->send($connection, [
            'type' => 'error',
            'code' => $code,
            'message' => $message,
        ]);
    }
}
