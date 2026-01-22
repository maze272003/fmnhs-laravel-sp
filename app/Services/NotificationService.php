<?php

namespace App\Services;

use App\Contracts\Repositories\StudentRepositoryInterface;
use App\Contracts\Repositories\TeacherRepositoryInterface;
use App\Contracts\Repositories\AssignmentRepositoryInterface;
use App\Contracts\Repositories\AnnouncementRepositoryInterface;
use App\Contracts\Services\NotificationServiceInterface;
use App\Mail\AnnouncementMail;
use App\Mail\NewAssignmentNotification;
use App\Mail\StudentAccountCreated;
use App\Support\Exceptions\ServiceException;
use Illuminate\Support\Facades\Mail;

class NotificationService extends BaseService implements NotificationServiceInterface
{
    public function __construct(
        protected StudentRepositoryInterface $studentRepository,
        protected TeacherRepositoryInterface $teacherRepository,
        protected AssignmentRepositoryInterface $assignmentRepository,
        protected AnnouncementRepositoryInterface $announcementRepository
    ) {}

    public function sendEmail(string $to, string $subject, string $view, array $data = []): bool
    {
        try {
            Mail::to($to)->send(new AnnouncementMail($subject, $view, $data));

            $this->logInfo('Email sent', ['to' => $to, 'subject' => $subject]);

            return true;
        } catch (Exception $e) {
            $this->handleException($e, 'Email sending failed');
            throw ServiceException::operationFailed('Failed to send email');
        }
    }

    public function sendWelcomeEmail(int $userId, string $role): bool
    {
        try {
            if ($role === 'student') {
                $user = $this->studentRepository->find($userId);
                $password = $user->lrn;

                Mail::to($user->email)->send(new StudentAccountCreated($user, $password));

                $this->logInfo('Welcome email sent', [
                    'user_id' => $userId,
                    'role' => $role,
                    'email' => $user->email,
                ]);

                return true;
            }

            throw ServiceException::validationFailed("Welcome email not implemented for role: {$role}");
        } catch (ServiceException $e) {
            throw $e;
        } catch (Exception $e) {
            $this->handleException($e, 'Welcome email sending failed');
            throw ServiceException::operationFailed('Failed to send welcome email');
        }
    }

    public function sendAssignmentNotification(int $assignmentId, array $studentIds): void
    {
        try {
            $assignment = $this->assignmentRepository->find($assignmentId);

            if (!$assignment) {
                throw ServiceException::modelNotFound('Assignment not found');
            }

            $students = $this->studentRepository->whereIn('id', $studentIds)->all();

            foreach ($students as $student) {
                Mail::to($student->email)->send(new NewAssignmentNotification($assignment, $student));
            }

            $this->logInfo('Assignment notifications sent', [
                'assignment_id' => $assignmentId,
                'student_count' => count($studentIds),
            ]);
        } catch (ServiceException $e) {
            throw $e;
        } catch (Exception $e) {
            $this->handleException($e, 'Assignment notification sending failed');
        }
    }

    public function sendAnnouncementEmail(int $announcementId, ?string $targetRole = null): void
    {
        try {
            $announcement = $this->announcementRepository->find($announcementId);

            if (!$announcement) {
                throw ServiceException::modelNotFound('Announcement not found');
            }

            $recipients = [];

            if (!$targetRole || $targetRole === 'student') {
                $students = $this->studentRepository->all();
                foreach ($students as $student) {
                    $recipients[] = $student->email;
                }
            }

            if (!$targetRole || $targetRole === 'teacher') {
                $teachers = $this->teacherRepository->all();
                foreach ($teachers as $teacher) {
                    $recipients[] = $teacher->email;
                }
            }

            foreach ($recipients as $email) {
                Mail::to($email)->send(new AnnouncementMail($announcement->title, 'emails.announcement', [
                    'announcement' => $announcement,
                ]));
            }

            $this->logInfo('Announcement emails sent', [
                'announcement_id' => $announcementId,
                'recipient_count' => count($recipients),
                'target_role' => $targetRole,
            ]);
        } catch (ServiceException $e) {
            throw $e;
        } catch (Exception $e) {
            $this->handleException($e, 'Announcement email sending failed');
        }
    }

    public function sendGradeUpdateNotification(int $studentId, int $subjectId, string $quarter): bool
    {
        try {
            $student = $this->studentRepository->find($studentId);

            if (!$student) {
                throw ServiceException::modelNotFound('Student not found');
            }

            Mail::to($student->email)->send(new AnnouncementMail(
                'Grade Update Notification',
                'emails.grade-update',
                [
                    'student' => $student,
                    'subject_id' => $subjectId,
                    'quarter' => $quarter,
                ]
            ));

            $this->logInfo('Grade update notification sent', [
                'student_id' => $studentId,
                'subject_id' => $subjectId,
                'quarter' => $quarter,
            ]);

            return true;
        } catch (ServiceException $e) {
            throw $e;
        } catch (Exception $e) {
            $this->handleException($e, 'Grade update notification sending failed');
            throw ServiceException::operationFailed('Failed to send grade update notification');
        }
    }
}
