<?php

namespace App\Services;

use App\Mail\ConferenceDigestMail;
use App\Models\ConferenceNotification;
use App\Models\VideoConference;
use Illuminate\Support\Facades\Mail;

class ConferenceNotificationService
{
    public function __construct(
        private readonly ConferenceDataService $dataService,
    ) {}

    /**
     * Notify the teacher when a participant joins.
     */
    public function notifyJoin(VideoConference $conference, string $actorId, string $displayName, string $role): void
    {
        if ($role === 'teacher') {
            return;
        }

        $this->createNotification($conference, 'teacher', $conference->teacher_id, 'join_alert', 'web', "{$displayName} joined the meeting.");
    }

    /**
     * Send network warning to a specific participant via signaling.
     */
    public function notifyNetworkWarning(VideoConference $conference, string $actorId, string $message): void
    {
        $this->createNotification($conference, 'system', 0, 'network_warning', 'web', $message, [
            'target_actor_id' => $actorId,
        ]);
    }

    /**
     * Notify about speaker attention (student not focused).
     */
    public function notifySpeakerAttention(VideoConference $conference, string $actorId, string $displayName): void
    {
        $this->createNotification($conference, 'teacher', $conference->teacher_id, 'speaker_attention', 'web', "{$displayName} may not be paying attention.", [
            'target_actor_id' => $actorId,
        ]);
    }

    /**
     * Send post-meeting digest email.
     */
    public function sendDigestEmail(VideoConference $conference): void
    {
        $summary = $this->dataService->getMeetingSummary($conference);
        $teacher = $conference->teacher;

        if (! $teacher || ! $teacher->email) {
            return;
        }

        try {
            Mail::to($teacher->email)->queue(new ConferenceDigestMail($conference, $summary));
        } catch (\Exception $e) {
            logger()->warning('Failed to send conference digest email', [
                'conference_id' => $conference->id,
                'error' => $e->getMessage(),
            ]);
        }

        $this->createNotification($conference, 'teacher', $conference->teacher_id, 'digest', 'email', 'Meeting digest sent to your email.');
    }

    /**
     * Send missed meeting notification to students who didn't join.
     */
    public function sendMissedMeetingNotifications(VideoConference $conference): void
    {
        if (! $conference->section_id) {
            return;
        }

        $joinedActorIds = $conference->participants()
            ->pluck('actor_id')
            ->map(fn ($id) => (int) str_replace('student-', '', $id))
            ->filter()
            ->unique()
            ->all();

        $missedStudents = $conference->section->students()
            ->whereNotIn('id', $joinedActorIds)
            ->get();

        foreach ($missedStudents as $student) {
            $this->createNotification(
                $conference,
                'student',
                $student->id,
                'missed',
                'web',
                "You missed the meeting: {$conference->title}",
                [
                    'started_at' => $conference->started_at?->toIso8601String(),
                    'ended_at' => $conference->ended_at?->toIso8601String(),
                ]
            );
        }
    }

    /**
     * Get unread notifications for a user.
     */
    public function getUnreadNotifications(string $recipientType, int $recipientId, int $limit = 20): array
    {
        return ConferenceNotification::with('conference')
            ->where('recipient_type', $recipientType)
            ->where('recipient_id', $recipientId)
            ->where('is_read', false)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(fn (ConferenceNotification $n) => [
                'id' => $n->id,
                'conference_id' => $n->conference_id,
                'conference_title' => $n->conference?->title,
                'type' => $n->type,
                'message' => $n->message,
                'metadata' => $n->metadata,
                'created_at' => $n->created_at->toIso8601String(),
            ])
            ->all();
    }

    /**
     * Mark notifications as read.
     */
    public function markAsRead(array $notificationIds, string $recipientType, int $recipientId): int
    {
        return ConferenceNotification::whereIn('id', $notificationIds)
            ->where('recipient_type', $recipientType)
            ->where('recipient_id', $recipientId)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);
    }

    /**
     * Create a notification record.
     */
    private function createNotification(
        VideoConference $conference,
        string $recipientType,
        int $recipientId,
        string $type,
        string $channel,
        string $message,
        ?array $metadata = null,
    ): ConferenceNotification {
        return ConferenceNotification::create([
            'conference_id' => $conference->id,
            'recipient_type' => $recipientType,
            'recipient_id' => $recipientId,
            'type' => $type,
            'channel' => $channel,
            'message' => $message,
            'metadata' => $metadata,
            'sent_at' => now(),
        ]);
    }
}
