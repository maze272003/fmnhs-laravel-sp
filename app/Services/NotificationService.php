<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Collection;
use Illuminate\Notifications\Notifiable;

class NotificationService
{
    /**
     * Send an email notification to a single recipient.
     *
     * @param mixed $recipient Email address or Notifiable entity
     * @param mixed $mailable Mailable instance
     * @return bool
     */
    public function sendEmail(mixed $recipient, mixed $mailable): bool
    {
        try {
            Mail::to($recipient)->send($mailable);
            return true;
        } catch (\Throwable $e) {
            logger()->error('Failed to send email notification', [
                'recipient' => is_string($recipient) ? $recipient : get_class($recipient),
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Send an email notification to multiple recipients.
     *
     * @param array $recipients Array of email addresses or Notifiable entities
     * @param mixed $mailable Mailable instance
     * @return int Number of successful sends
     */
    public function sendEmailToMany(array $recipients, mixed $mailable): int
    {
        $successCount = 0;

        foreach ($recipients as $recipient) {
            if ($this->sendEmail($recipient, $mailable)) {
                $successCount++;
            }
        }

        return $successCount;
    }

    /**
     * Send a database notification to users.
     *
     * @param mixed $notifiable User or collection of users
     * @param mixed $notification Notification instance
     * @return void
     */
    public function sendDatabaseNotification(mixed $notifiable, mixed $notification): void
    {
        try {
            if ($notifiable instanceof Collection) {
                $notifiable->each(fn ($user) => $user->notify($notification));
            } else {
                $notifiable->notify($notification);
            }
        } catch (\Throwable $e) {
            logger()->error('Failed to send database notification', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send a notification to all users of a specific type.
     *
     * @param string $userType User model class (Student, Teacher, Admin, ParentModel)
     * @param mixed $notification Notification instance
     * @param array $conditions Additional query conditions
     * @return int Number of notifications sent
     */
    public function notifyAllOfType(string $userType, mixed $notification, array $conditions = []): int
    {
        try {
            $query = $userType::query();

            foreach ($conditions as $field => $value) {
                $query->where($field, $value);
            }

            $users = $query->get();
            $users->each(fn ($user) => $user->notify($notification));

            return $users->count();
        } catch (\Throwable $e) {
            logger()->error('Failed to send bulk notifications', [
                'user_type' => $userType,
                'error' => $e->getMessage(),
            ]);
            return 0;
        }
    }

    /**
     * Send an announcement notification to target audience.
     *
     * @param string $targetAudience Target audience (all, students, teachers, parents)
     * @param mixed $mailable Mailable instance
     * @return array{sent: int, failed: int}
     */
    public function sendAnnouncementNotification(string $targetAudience, mixed $mailable): array
    {
        $result = ['sent' => 0, 'failed' => 0];

        $userTypes = match ($targetAudience) {
            'students' => [\App\Models\Student::class],
            'teachers' => [\App\Models\Teacher::class],
            'parents' => [\App\Models\ParentModel::class],
            'all' => [\App\Models\Student::class, \App\Models\Teacher::class, \App\Models\ParentModel::class],
            default => [],
        };

        foreach ($userTypes as $userType) {
            $users = $userType::whereNotNull('email')->get();

            foreach ($users as $user) {
                if ($this->sendEmail($user->email, $mailable)) {
                    $result['sent']++;
                } else {
                    $result['failed']++;
                }
            }
        }

        return $result;
    }

    /**
     * Mark notifications as read for a user.
     *
     * @param mixed $notifiable Notifiable entity
     * @param array|null $notificationIds Specific notification IDs to mark (null for all)
     * @return int Number of notifications marked as read
     */
    public function markAsRead(mixed $notifiable, ?array $notificationIds = null): int
    {
        $query = $notifiable->unreadNotifications();

        if ($notificationIds !== null) {
            $query->whereIn('id', $notificationIds);
        }

        return $query->update(['read_at' => now()]);
    }

    /**
     * Get unread notifications count for a user.
     *
     * @param mixed $notifiable Notifiable entity
     * @return int
     */
    public function getUnreadCount(mixed $notifiable): int
    {
        return $notifiable->unreadNotifications()->count();
    }

    /**
     * Clear old read notifications.
     *
     * @param int $daysOld Number of days to keep read notifications
     * @return int Number of notifications deleted
     */
    public function clearOldNotifications(int $daysOld = 30): int
    {
        $cutoffDate = now()->subDays($daysOld);

        return \Illuminate\Notifications\DatabaseNotification::whereNotNull('read_at')
            ->where('created_at', '<', $cutoffDate)
            ->delete();
    }
}
