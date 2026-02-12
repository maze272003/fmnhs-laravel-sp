<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AssignmentReminderService
{
    /**
     * Send a reminder to students about an assignment.
     */
    public function sendReminder(Assignment $assignment, Collection $students): array
    {
        $sent = 0;
        $failed = 0;

        foreach ($students as $student) {
            if (!$student->email) {
                $failed++;
                continue;
            }

            try {
                Mail::raw(
                    "Reminder: The assignment \"{$assignment->title}\" is due on {$assignment->deadline}. Please submit your work before the deadline.",
                    function ($message) use ($student, $assignment) {
                        $message->to($student->email)
                            ->subject("Assignment Reminder: {$assignment->title}");
                    }
                );
                $sent++;
            } catch (\Throwable $e) {
                Log::error('Failed to send assignment reminder', [
                    'student_id' => $student->id,
                    'assignment_id' => $assignment->id,
                    'error' => $e->getMessage(),
                ]);
                $failed++;
            }
        }

        return [
            'assignment_id' => $assignment->id,
            'total_students' => $students->count(),
            'sent' => $sent,
            'failed' => $failed,
        ];
    }

    /**
     * Check for upcoming deadlines and return assignments due within the next 24 hours.
     */
    public function checkUpcomingDeadlines(): Collection
    {
        return Assignment::where('deadline', '>=', now())
            ->where('deadline', '<=', now()->addHours(24))
            ->with(['section.students', 'subject'])
            ->get();
    }

    /**
     * Send notifications for late submissions.
     */
    public function sendLateNotifications(): array
    {
        $overdueAssignments = $this->getOverdueAssignments();
        $notificationsSent = 0;

        foreach ($overdueAssignments as $assignment) {
            $submittedStudentIds = $assignment->submissions()->pluck('student_id');
            $lateStudents = $assignment->section->students()
                ->whereNotIn('id', $submittedStudentIds)
                ->get();

            foreach ($lateStudents as $student) {
                if (!$student->email) {
                    continue;
                }

                try {
                    Mail::raw(
                        "Your assignment \"{$assignment->title}\" is past due. The deadline was {$assignment->deadline}. Please submit as soon as possible.",
                        function ($message) use ($student, $assignment) {
                            $message->to($student->email)
                                ->subject("Late Assignment: {$assignment->title}");
                        }
                    );
                    $notificationsSent++;
                } catch (\Throwable $e) {
                    Log::error('Failed to send late notification', [
                        'student_id' => $student->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        return [
            'overdue_assignments' => $overdueAssignments->count(),
            'notifications_sent' => $notificationsSent,
        ];
    }

    /**
     * Get all overdue assignments, optionally filtered by teacher.
     */
    public function getOverdueAssignments(?Teacher $teacher = null): Collection
    {
        $query = Assignment::where('deadline', '<', now())
            ->with(['section.students', 'submissions']);

        if ($teacher) {
            $query->where('teacher_id', $teacher->id);
        }

        return $query->get()->filter(function (Assignment $assignment) {
            $submittedCount = $assignment->submissions->count();
            $totalStudents = $assignment->section?->students()->count() ?? 0;

            return $submittedCount < $totalStudents;
        })->values();
    }
}
