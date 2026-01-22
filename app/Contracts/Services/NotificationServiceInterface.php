<?php

namespace App\Contracts\Services;

interface NotificationServiceInterface
{
    public function sendEmail(string $to, string $subject, string $view, array $data = []): bool;
    public function sendWelcomeEmail(int $userId, string $role): bool;
    public function sendAssignmentNotification(int $assignmentId, array $studentIds): void;
    public function sendAnnouncementEmail(int $announcementId, ?string $targetRole = null): void;
    public function sendGradeUpdateNotification(int $studentId, int $subjectId, string $quarter): bool;
}
