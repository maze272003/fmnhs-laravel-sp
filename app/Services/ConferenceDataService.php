<?php

namespace App\Services;

use App\Models\ConferenceEvent;
use App\Models\ConferenceMessage;
use App\Models\ConferenceParticipant;
use App\Models\ConferenceRecording;
use App\Models\VideoConference;
use Illuminate\Support\Facades\Storage;

class ConferenceDataService
{
    /**
     * Record a participant joining a conference.
     */
    public function recordJoin(VideoConference $conference, string $actorId, string $displayName, string $role, bool $isGuest = false, ?array $deviceInfo = null): ConferenceParticipant
    {
        // Close any previous open participation for this actor
        ConferenceParticipant::where('conference_id', $conference->id)
            ->where('actor_id', $actorId)
            ->whereNull('left_at')
            ->update([
                'left_at' => now(),
                'duration_seconds' => \DB::raw('TIMESTAMPDIFF(SECOND, joined_at, NOW())'),
            ]);

        $participant = ConferenceParticipant::create([
            'conference_id' => $conference->id,
            'actor_id' => $actorId,
            'actor_type' => str_contains($actorId, 'teacher') ? 'teacher' : (str_contains($actorId, 'guest') ? 'guest' : 'student'),
            'display_name' => $displayName,
            'role' => $role,
            'joined_at' => now(),
            'is_guest' => $isGuest,
            'device_info' => $deviceInfo,
        ]);

        $this->logEvent($conference, $actorId, 'join', ['display_name' => $displayName, 'role' => $role]);

        return $participant;
    }

    /**
     * Record a participant leaving.
     */
    public function recordLeave(VideoConference $conference, string $actorId): void
    {
        $participant = ConferenceParticipant::where('conference_id', $conference->id)
            ->where('actor_id', $actorId)
            ->whereNull('left_at')
            ->latest()
            ->first();

        if ($participant) {
            $duration = $participant->joined_at
                ? (int) $participant->joined_at->diffInSeconds(now())
                : 0;

            $participant->update([
                'left_at' => now(),
                'duration_seconds' => $duration,
            ]);
        }

        $this->logEvent($conference, $actorId, 'leave');
    }

    /**
     * Save a chat message.
     */
    public function saveMessage(VideoConference $conference, string $actorId, string $displayName, string $role, string $content, string $type = 'text'): ConferenceMessage
    {
        return ConferenceMessage::create([
            'conference_id' => $conference->id,
            'actor_id' => $actorId,
            'display_name' => $displayName,
            'role' => $role,
            'content' => $content,
            'type' => $type,
            'conference_elapsed_seconds' => $conference->getCurrentElapsedSeconds(),
        ]);
    }

    /**
     * Save a file message (uploaded to S3).
     */
    public function saveFileMessage(
        VideoConference $conference,
        string $actorId,
        string $displayName,
        string $role,
        string $filePath,
        string $fileName,
        string $fileMime,
        int $fileSize,
    ): ConferenceMessage {
        return ConferenceMessage::create([
            'conference_id' => $conference->id,
            'actor_id' => $actorId,
            'display_name' => $displayName,
            'role' => $role,
            'content' => $fileName,
            'type' => 'file',
            'file_path' => $filePath,
            'file_name' => $fileName,
            'file_mime' => $fileMime,
            'file_size' => $fileSize,
            'conference_elapsed_seconds' => $conference->getCurrentElapsedSeconds(),
        ]);
    }

    /**
     * Log a conference event.
     */
    public function logEvent(VideoConference $conference, ?string $actorId, string $eventType, ?array $metadata = null): ConferenceEvent
    {
        return ConferenceEvent::create([
            'conference_id' => $conference->id,
            'actor_id' => $actorId,
            'event_type' => $eventType,
            'metadata' => $metadata,
            'conference_elapsed_seconds' => $conference->getCurrentElapsedSeconds(),
        ]);
    }

    /**
     * Get the full chat replay for a conference with timestamps.
     */
    public function getChatReplay(VideoConference $conference): array
    {
        return $conference->messages()
            ->orderBy('created_at')
            ->get()
            ->map(fn (ConferenceMessage $m) => [
                'id' => $m->id,
                'actor_id' => $m->actor_id,
                'display_name' => $m->display_name,
                'role' => $m->role,
                'content' => $m->content,
                'type' => $m->type,
                'file_url' => $m->isFile() ? $this->getFileUrl($m) : null,
                'file_name' => $m->file_name,
                'file_mime' => $m->file_mime,
                'file_size' => $m->file_size,
                'elapsed_seconds' => $m->conference_elapsed_seconds,
                'timestamp' => $m->created_at->toIso8601String(),
            ])
            ->all();
    }

    /**
     * Get the event timeline for a conference.
     */
    public function getEventTimeline(VideoConference $conference): array
    {
        return $conference->events()
            ->orderBy('created_at')
            ->get()
            ->map(fn (ConferenceEvent $e) => [
                'id' => $e->id,
                'actor_id' => $e->actor_id,
                'event_type' => $e->event_type,
                'metadata' => $e->metadata,
                'elapsed_seconds' => $e->conference_elapsed_seconds,
                'timestamp' => $e->created_at->toIso8601String(),
            ])
            ->all();
    }

    /**
     * Get participant attendance summary.
     */
    public function getAttendanceSummary(VideoConference $conference): array
    {
        return $conference->participants()
            ->orderBy('joined_at')
            ->get()
            ->groupBy('actor_id')
            ->map(function ($participations, $actorId) {
                $totalDuration = $participations->sum('duration_seconds');
                $first = $participations->first();

                return [
                    'actor_id' => $actorId,
                    'display_name' => $first->display_name,
                    'role' => $first->role,
                    'is_guest' => $first->is_guest,
                    'total_duration_seconds' => $totalDuration,
                    'join_count' => $participations->count(),
                    'first_joined' => $first->joined_at?->toIso8601String(),
                    'last_left' => $participations->last()->left_at?->toIso8601String(),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * Get a temporary URL for a file attachment.
     */
    public function getFileUrl(ConferenceMessage $message): ?string
    {
        if (! $message->isFile() || ! $message->file_path) {
            return null;
        }

        $disk = config('conference.file_disk', 's3');

        try {
            return Storage::disk($disk)->temporaryUrl($message->file_path, now()->addHour());
        } catch (\Exception) {
            return Storage::disk($disk)->url($message->file_path);
        }
    }

    /**
     * End meeting and close all open participations.
     */
    public function endMeeting(VideoConference $conference): void
    {
        // Close all open participations
        ConferenceParticipant::where('conference_id', $conference->id)
            ->whereNull('left_at')
            ->update([
                'left_at' => now(),
                'duration_seconds' => \DB::raw('TIMESTAMPDIFF(SECOND, joined_at, NOW())'),
            ]);

        $this->logEvent($conference, null, 'meeting_ended');

        $conference->update([
            'is_active' => false,
            'ended_at' => now(),
        ]);
    }

    /**
     * Generate meeting summary/digest.
     */
    public function getMeetingSummary(VideoConference $conference): array
    {
        $conference->loadMissing(['teacher', 'section']);

        return [
            'conference' => [
                'id' => $conference->id,
                'title' => $conference->title,
                'teacher' => $conference->teacher
                    ? trim($conference->teacher->first_name.' '.$conference->teacher->last_name)
                    : null,
                'section' => $conference->section?->name,
                'started_at' => $conference->started_at?->toIso8601String(),
                'ended_at' => $conference->ended_at?->toIso8601String(),
                'duration_seconds' => $conference->getElapsedSeconds(),
            ],
            'attendance' => $this->getAttendanceSummary($conference),
            'message_count' => $conference->messages()->count(),
            'event_count' => $conference->events()->count(),
            'recordings' => $conference->recordings()
                ->where('status', 'ready')
                ->get()
                ->map(fn (ConferenceRecording $r) => [
                    'id' => $r->id,
                    'title' => $r->title,
                    'type' => $r->type,
                    'duration_seconds' => $r->duration_seconds,
                    'download_url' => $r->getDownloadUrl(),
                ])
                ->all(),
        ];
    }
}
