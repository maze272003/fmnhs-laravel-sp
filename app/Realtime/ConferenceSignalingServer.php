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
            'push-to-talk' => $this->broadcastPushToTalk($connection, $message),
            'annotation' => $this->broadcastAnnotation($connection, $message),
            'laser-pointer' => $this->broadcastLaserPointer($connection, $message),
            'remote-control-request' => $this->handleRemoteControlRequest($connection, $message),
            'remote-control-response' => $this->handleRemoteControlResponse($connection, $message),
            'remote-control-action' => $this->forwardRemoteControlAction($connection, $message),
            'remote-control-stop' => $this->broadcastRemoteControlStop($connection),
            'presentation-mode' => $this->broadcastPresentationMode($connection, $message),
            'recording-started' => $this->broadcastRecordingEvent($connection, 'recording-started'),
            'recording-stopped' => $this->broadcastRecordingEvent($connection, 'recording-stopped'),
            'video-freeze-detected' => $this->handleVideoFreezeDetected($connection, $message),
            'network-quality' => $this->handleNetworkQuality($connection, $message),
            'attention-check' => $this->broadcastAttentionCheck($connection, $message),
            'silent-join' => $this->handleSilentJoinToggle($connection, $message),
            'kick-participant' => $this->handleKickParticipant($connection, $message),
            'mute-all' => $this->handleMuteAll($connection),
            'file-shared' => $this->broadcastFileShared($connection, $message),
            'quiz-started', 'quiz-question', 'quiz-question-ended', 'quiz-ended', 'quiz-results', 'quiz-leaderboard' 
                => $this->broadcastQuizEvent($connection, $type, $message),
            'whiteboard-draw', 'whiteboard-clear', 'whiteboard-undo', 'whiteboard-sync' 
                => $this->broadcastWhiteboardEvent($connection, $type, $message),
            'breakout-created', 'breakout-assigned', 'breakout-joined', 'breakout-left', 'breakout-ended', 'breakout-broadcast', 'breakout-timer' 
                => $this->broadcastBreakoutEvent($connection, $type, $message),
            'mood-speed', 'mood-understanding', 'mood-confidence', 'mood-aggregate' 
                => $this->broadcastMoodEvent($connection, $type, $message),
            'game-started', 'game-state', 'game-action', 'game-ended', 'game-scores' 
                => $this->broadcastGameEvent($connection, $type, $message),
            'caption', 'caption-clear' 
                => $this->broadcastCaptionEvent($connection, $type, $message),
            'presentation-started', 'slide-changed', 'slide-annotate', 'presentation-ended', 'slide-progress' 
                => $this->broadcastPresentationEvent($connection, $type, $message),
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

    /** @param array<string, mixed> $message */
    private function broadcastPushToTalk(ConnectionInterface $connection, array $message): void
    {
        $state = $this->connections[$connection->id] ?? null;
        if (! is_array($state)) return;
        $roomId = (string) ($state['roomId'] ?? '');
        if ($roomId === '') return;
        $active = (bool) ($message['active'] ?? false);
        $this->broadcastToRoom($roomId, [
            'type' => 'push-to-talk',
            'roomId' => $roomId,
            'from' => $this->participantFromState($state),
            'active' => $active,
        ], $connection);
    }

    /** @param array<string, mixed> $message */
    private function broadcastAnnotation(ConnectionInterface $connection, array $message): void
    {
        $state = $this->connections[$connection->id] ?? null;
        if (! is_array($state)) return;
        $roomId = (string) ($state['roomId'] ?? '');
        if ($roomId === '') return;
        $data = $message['data'] ?? null;
        if (! is_array($data)) return;
        $this->broadcastToRoom($roomId, [
            'type' => 'annotation',
            'roomId' => $roomId,
            'from' => $this->participantFromState($state),
            'data' => $data,
        ], $connection);
    }

    /** @param array<string, mixed> $message */
    private function broadcastLaserPointer(ConnectionInterface $connection, array $message): void
    {
        $state = $this->connections[$connection->id] ?? null;
        if (! is_array($state)) return;
        $roomId = (string) ($state['roomId'] ?? '');
        if ($roomId === '') return;
        $this->broadcastToRoom($roomId, [
            'type' => 'laser-pointer',
            'roomId' => $roomId,
            'from' => $this->participantFromState($state),
            'x' => (float) ($message['x'] ?? 0),
            'y' => (float) ($message['y'] ?? 0),
            'visible' => (bool) ($message['visible'] ?? true),
        ], $connection);
    }

    /** @param array<string, mixed> $message */
    private function handleRemoteControlRequest(ConnectionInterface $connection, array $message): void
    {
        $state = $this->connections[$connection->id] ?? null;
        if (! is_array($state)) return;
        if (($state['role'] ?? '') !== 'student') {
            $this->sendError($connection, 'forbidden', 'Only students can request remote control.');
            return;
        }
        $roomId = (string) ($state['roomId'] ?? '');
        if ($roomId === '') return;
        // Forward to teacher(s) in the room
        foreach ($this->rooms[$roomId] ?? [] as $peerId => $peerConn) {
            $peerState = $this->connections[$peerConn->id] ?? null;
            if (is_array($peerState) && ($peerState['role'] ?? '') === 'teacher') {
                $this->send($peerConn, [
                    'type' => 'remote-control-request',
                    'roomId' => $roomId,
                    'from' => $this->participantFromState($state),
                ]);
            }
        }
    }

    /** @param array<string, mixed> $message */
    private function handleRemoteControlResponse(ConnectionInterface $connection, array $message): void
    {
        $state = $this->connections[$connection->id] ?? null;
        if (! is_array($state)) return;
        if (($state['role'] ?? '') !== 'teacher') {
            $this->sendError($connection, 'forbidden', 'Only the teacher can approve remote control.');
            return;
        }
        $roomId = (string) ($state['roomId'] ?? '');
        $targetId = trim((string) ($message['targetId'] ?? ''));
        $approved = (bool) ($message['approved'] ?? false);
        if ($roomId === '' || $targetId === '' || ! isset($this->rooms[$roomId][$targetId])) return;
        $this->send($this->rooms[$roomId][$targetId], [
            'type' => 'remote-control-response',
            'roomId' => $roomId,
            'from' => $this->participantFromState($state),
            'approved' => $approved,
        ]);
        if ($approved) {
            $this->broadcastToRoom($roomId, [
                'type' => 'remote-control-granted',
                'roomId' => $roomId,
                'targetId' => $targetId,
                'from' => $this->participantFromState($state),
            ]);
        }
    }

    /** @param array<string, mixed> $message */
    private function forwardRemoteControlAction(ConnectionInterface $connection, array $message): void
    {
        $state = $this->connections[$connection->id] ?? null;
        if (! is_array($state)) return;
        $roomId = (string) ($state['roomId'] ?? '');
        $targetId = trim((string) ($message['targetId'] ?? ''));
        if ($roomId === '' || $targetId === '' || ! isset($this->rooms[$roomId][$targetId])) return;
        $this->send($this->rooms[$roomId][$targetId], [
            'type' => 'remote-control-action',
            'roomId' => $roomId,
            'from' => $this->participantFromState($state),
            'action' => $message['action'] ?? null,
        ]);
    }

    private function broadcastRemoteControlStop(ConnectionInterface $connection): void
    {
        $state = $this->connections[$connection->id] ?? null;
        if (! is_array($state)) return;
        $roomId = (string) ($state['roomId'] ?? '');
        if ($roomId === '') return;
        $this->broadcastToRoom($roomId, [
            'type' => 'remote-control-stop',
            'roomId' => $roomId,
            'from' => $this->participantFromState($state),
        ]);
    }

    /** @param array<string, mixed> $message */
    private function broadcastPresentationMode(ConnectionInterface $connection, array $message): void
    {
        $state = $this->connections[$connection->id] ?? null;
        if (! is_array($state)) return;
        if (($state['role'] ?? '') !== 'teacher') {
            $this->sendError($connection, 'forbidden', 'Only the teacher can control presentation mode.');
            return;
        }
        $roomId = (string) ($state['roomId'] ?? '');
        if ($roomId === '') return;
        $this->broadcastToRoom($roomId, [
            'type' => 'presentation-mode',
            'roomId' => $roomId,
            'from' => $this->participantFromState($state),
            'active' => (bool) ($message['active'] ?? false),
            'slide' => (int) ($message['slide'] ?? 0),
        ]);
    }

    private function broadcastRecordingEvent(ConnectionInterface $connection, string $eventType): void
    {
        $state = $this->connections[$connection->id] ?? null;
        if (! is_array($state)) return;
        if (($state['role'] ?? '') !== 'teacher') {
            $this->sendError($connection, 'forbidden', 'Only the teacher can control recording.');
            return;
        }
        $roomId = (string) ($state['roomId'] ?? '');
        if ($roomId === '') return;
        $this->broadcastToRoom($roomId, [
            'type' => $eventType,
            'roomId' => $roomId,
            'from' => $this->participantFromState($state),
        ]);
    }

    /** @param array<string, mixed> $message */
    private function handleVideoFreezeDetected(ConnectionInterface $connection, array $message): void
    {
        $state = $this->connections[$connection->id] ?? null;
        if (! is_array($state)) return;
        $roomId = (string) ($state['roomId'] ?? '');
        if ($roomId === '') return;
        // Notify teacher about frozen video
        foreach ($this->rooms[$roomId] ?? [] as $peerId => $peerConn) {
            $peerState = $this->connections[$peerConn->id] ?? null;
            if (is_array($peerState) && ($peerState['role'] ?? '') === 'teacher') {
                $this->send($peerConn, [
                    'type' => 'video-freeze-alert',
                    'roomId' => $roomId,
                    'from' => $this->participantFromState($state),
                    'peerId' => trim((string) ($message['peerId'] ?? '')),
                ]);
            }
        }
    }

    /** @param array<string, mixed> $message */
    private function handleNetworkQuality(ConnectionInterface $connection, array $message): void
    {
        $state = $this->connections[$connection->id] ?? null;
        if (! is_array($state)) return;
        $roomId = (string) ($state['roomId'] ?? '');
        if ($roomId === '') return;
        // Only forward to teacher for monitoring
        foreach ($this->rooms[$roomId] ?? [] as $peerId => $peerConn) {
            $peerState = $this->connections[$peerConn->id] ?? null;
            if (is_array($peerState) && ($peerState['role'] ?? '') === 'teacher' && $peerConn !== $connection) {
                $this->send($peerConn, [
                    'type' => 'network-quality-report',
                    'roomId' => $roomId,
                    'from' => $this->participantFromState($state),
                    'quality' => $message['quality'] ?? 'unknown',
                    'stats' => $message['stats'] ?? null,
                ]);
            }
        }
    }

    /** @param array<string, mixed> $message */
    private function broadcastAttentionCheck(ConnectionInterface $connection, array $message): void
    {
        $state = $this->connections[$connection->id] ?? null;
        if (! is_array($state)) return;
        if (($state['role'] ?? '') !== 'teacher') {
            $this->sendError($connection, 'forbidden', 'Only the teacher can send attention checks.');
            return;
        }
        $roomId = (string) ($state['roomId'] ?? '');
        if ($roomId === '') return;
        $this->broadcastToRoom($roomId, [
            'type' => 'attention-check',
            'roomId' => $roomId,
            'from' => $this->participantFromState($state),
            'message' => mb_substr(trim((string) ($message['message'] ?? 'Are you paying attention?')), 0, 200),
        ], $connection);
    }

    /** @param array<string, mixed> $message */
    private function handleSilentJoinToggle(ConnectionInterface $connection, array $message): void
    {
        // Silent join: don't broadcast join notification
        $state = $this->connections[$connection->id] ?? null;
        if (! is_array($state)) return;
        // Store silent preference on connection state
        $this->connections[$connection->id]['silent'] = (bool) ($message['enabled'] ?? false);
    }

    /** @param array<string, mixed> $message */
    private function handleKickParticipant(ConnectionInterface $connection, array $message): void
    {
        $state = $this->connections[$connection->id] ?? null;
        if (! is_array($state)) return;
        if (($state['role'] ?? '') !== 'teacher') {
            $this->sendError($connection, 'forbidden', 'Only the teacher can kick participants.');
            return;
        }
        $roomId = (string) ($state['roomId'] ?? '');
        $targetId = trim((string) ($message['targetId'] ?? ''));
        if ($roomId === '' || $targetId === '' || ! isset($this->rooms[$roomId][$targetId])) return;
        $targetConn = $this->rooms[$roomId][$targetId];
        $this->send($targetConn, [
            'type' => 'kicked',
            'roomId' => $roomId,
            'from' => $this->participantFromState($state),
            'reason' => mb_substr(trim((string) ($message['reason'] ?? '')), 0, 200),
        ]);
        $targetConn->close();
    }

    private function handleMuteAll(ConnectionInterface $connection): void
    {
        $state = $this->connections[$connection->id] ?? null;
        if (! is_array($state)) return;
        if (($state['role'] ?? '') !== 'teacher') {
            $this->sendError($connection, 'forbidden', 'Only the teacher can mute all.');
            return;
        }
        $roomId = (string) ($state['roomId'] ?? '');
        if ($roomId === '') return;
        $this->broadcastToRoom($roomId, [
            'type' => 'force-mute-all',
            'roomId' => $roomId,
            'from' => $this->participantFromState($state),
        ], $connection);
    }

    /** @param array<string, mixed> $message */
    private function broadcastFileShared(ConnectionInterface $connection, array $message): void
    {
        $state = $this->connections[$connection->id] ?? null;
        if (! is_array($state)) return;
        $roomId = (string) ($state['roomId'] ?? '');
        if ($roomId === '') return;
        $this->broadcastToRoom($roomId, [
            'type' => 'file-shared',
            'roomId' => $roomId,
            'from' => $this->participantFromState($state),
            'fileName' => mb_substr(trim((string) ($message['fileName'] ?? '')), 0, 255),
            'fileUrl' => trim((string) ($message['fileUrl'] ?? '')),
            'fileMime' => trim((string) ($message['fileMime'] ?? '')),
            'fileSize' => (int) ($message['fileSize'] ?? 0),
        ]);
    }

    private function broadcastQuizEvent(ConnectionInterface $connection, string $type, array $message): void
    {
        $state = $this->connections[$connection->id] ?? null;
        if (! is_array($state)) return;
        if (($state['role'] ?? '') !== 'teacher') {
            $this->sendError($connection, 'forbidden', 'Only the teacher can control quizzes.');
            return;
        }
        $roomId = (string) ($state['roomId'] ?? '');
        if ($roomId === '') return;
        $this->broadcastToRoom($roomId, [
            'type' => $type,
            'roomId' => $roomId,
            'from' => $this->participantFromState($state),
            'quiz' => $message['quiz'] ?? null,
            'question' => $message['question'] ?? null,
            'questionIndex' => $message['questionIndex'] ?? null,
            'results' => $message['results'] ?? null,
            'leaderboard' => $message['leaderboard'] ?? null,
        ]);
    }

    private function broadcastWhiteboardEvent(ConnectionInterface $connection, string $type, array $message): void
    {
        $state = $this->connections[$connection->id] ?? null;
        if (! is_array($state)) return;
        $roomId = (string) ($state['roomId'] ?? '');
        if ($roomId === '') return;
        $this->broadcastToRoom($roomId, [
            'type' => $type,
            'roomId' => $roomId,
            'from' => $this->participantFromState($state),
            'stroke' => $message['stroke'] ?? null,
            'strokes' => $message['strokes'] ?? null,
        ], $connection);
    }

    private function broadcastBreakoutEvent(ConnectionInterface $connection, string $type, array $message): void
    {
        $state = $this->connections[$connection->id] ?? null;
        if (! is_array($state)) return;
        if (in_array($type, ['breakout-created', 'breakout-assigned', 'breakout-ended', 'breakout-timer']) && ($state['role'] ?? '') !== 'teacher') {
            $this->sendError($connection, 'forbidden', 'Only the teacher can manage breakout rooms.');
            return;
        }
        $roomId = (string) ($state['roomId'] ?? '');
        if ($roomId === '') return;
        $this->broadcastToRoom($roomId, [
            'type' => $type,
            'roomId' => $roomId,
            'from' => $this->participantFromState($state),
            'rooms' => $message['rooms'] ?? null,
            'assignments' => $message['assignments'] ?? null,
            'participantId' => $message['participantId'] ?? null,
            'roomId' => $message['roomId'] ?? null,
            'message' => $message['message'] ?? null,
            'duration' => $message['duration'] ?? null,
            'participant' => $message['participant'] ?? null,
        ]);
    }

    private function broadcastMoodEvent(ConnectionInterface $connection, string $type, array $message): void
    {
        $state = $this->connections[$connection->id] ?? null;
        if (! is_array($state)) return;
        $roomId = (string) ($state['roomId'] ?? '');
        if ($roomId === '') return;
        $this->broadcastToRoom($roomId, [
            'type' => $type,
            'roomId' => $roomId,
            'from' => $this->participantFromState($state),
            'speed' => $message['speed'] ?? null,
            'level' => $message['level'] ?? null,
            'data' => $message['data'] ?? null,
        ]);
    }

    private function broadcastGameEvent(ConnectionInterface $connection, string $type, array $message): void
    {
        $state = $this->connections[$connection->id] ?? null;
        if (! is_array($state)) return;
        if (in_array($type, ['game-started', 'game-ended']) && ($state['role'] ?? '') !== 'teacher') {
            $this->sendError($connection, 'forbidden', 'Only the teacher can control games.');
            return;
        }
        $roomId = (string) ($state['roomId'] ?? '');
        if ($roomId === '') return;
        $this->broadcastToRoom($roomId, [
            'type' => $type,
            'roomId' => $roomId,
            'from' => $this->participantFromState($state),
            'game' => $message['game'] ?? null,
            'state' => $message['state'] ?? null,
            'action' => $message['action'] ?? null,
            'data' => $message['data'] ?? null,
            'scores' => $message['scores'] ?? null,
            'results' => $message['results'] ?? null,
        ]);
    }

    private function broadcastCaptionEvent(ConnectionInterface $connection, string $type, array $message): void
    {
        $state = $this->connections[$connection->id] ?? null;
        if (! is_array($state)) return;
        $roomId = (string) ($state['roomId'] ?? '');
        if ($roomId === '') return;
        $this->broadcastToRoom($roomId, [
            'type' => $type,
            'roomId' => $roomId,
            'from' => $this->participantFromState($state),
            'caption' => $message['caption'] ?? null,
        ]);
    }

    private function broadcastPresentationEvent(ConnectionInterface $connection, string $type, array $message): void
    {
        $state = $this->connections[$connection->id] ?? null;
        if (! is_array($state)) return;
        if (in_array($type, ['presentation-started', 'presentation-ended']) && ($state['role'] ?? '') !== 'teacher') {
            $this->sendError($connection, 'forbidden', 'Only the teacher can control presentations.');
            return;
        }
        $roomId = (string) ($state['roomId'] ?? '');
        if ($roomId === '') return;
        $this->broadcastToRoom($roomId, [
            'type' => $type,
            'roomId' => $roomId,
            'from' => $this->participantFromState($state),
            'presentation' => $message['presentation'] ?? null,
            'slides' => $message['slides'] ?? null,
            'currentSlide' => $message['currentSlide'] ?? null,
            'slideIndex' => $message['slideIndex'] ?? null,
            'slide' => $message['slide'] ?? null,
            'annotation' => $message['annotation'] ?? null,
            'action' => $message['action'] ?? null,
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
