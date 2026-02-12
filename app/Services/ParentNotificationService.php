<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\Attendance;
use App\Models\Grade;
use App\Models\ParentModel;
use App\Models\Student;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ParentNotificationService
{
    /**
     * Notify parents about a student's absence.
     */
    public function notifyAbsence(Student $student, string $date): bool
    {
        $parents = $student->parents ?? collect();
        if ($parents->isEmpty()) {
            return false;
        }

        $studentName = "{$student->first_name} {$student->last_name}";
        $sent = false;

        foreach ($parents as $parent) {
            if (!$parent->email) {
                continue;
            }

            try {
                Mail::raw(
                    "Dear {$parent->name},\n\nThis is to inform you that your child, {$studentName}, was marked absent on {$date}. Please contact the school if you have any concerns.\n\nThank you.",
                    function ($message) use ($parent, $studentName) {
                        $message->to($parent->email)
                            ->subject("Absence Notice: {$studentName}");
                    }
                );
                $sent = true;
            } catch (\Throwable $e) {
                Log::error('Failed to send absence notification', [
                    'parent_id' => $parent->id,
                    'student_id' => $student->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $sent;
    }

    /**
     * Notify parents about a late assignment submission.
     */
    public function notifyLateSubmission(Student $student, Assignment $assignment): bool
    {
        $parents = $student->parents ?? collect();
        if ($parents->isEmpty()) {
            return false;
        }

        $studentName = "{$student->first_name} {$student->last_name}";
        $sent = false;

        foreach ($parents as $parent) {
            if (!$parent->email) {
                continue;
            }

            try {
                Mail::raw(
                    "Dear {$parent->name},\n\nThis is to inform you that {$studentName} has not yet submitted the assignment \"{$assignment->title}\" which was due on {$assignment->deadline}.\n\nPlease encourage timely submission.\n\nThank you.",
                    function ($message) use ($parent, $studentName) {
                        $message->to($parent->email)
                            ->subject("Late Assignment Notice: {$studentName}");
                    }
                );
                $sent = true;
            } catch (\Throwable $e) {
                Log::error('Failed to send late submission notification', [
                    'parent_id' => $parent->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $sent;
    }

    /**
     * Send a progress update to a parent about their student.
     */
    public function sendProgressUpdate(ParentModel $parent, Student $student): bool
    {
        $grades = Grade::where('student_id', $student->id)
            ->with('subject')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $avgGrade = $grades->isNotEmpty() ? round($grades->avg('grade_value'), 2) : 'N/A';
        $studentName = "{$student->first_name} {$student->last_name}";

        $attendances = Attendance::where('student_id', $student->id)
            ->where('date', '>=', now()->subDays(30))
            ->get();
        $totalClasses = $attendances->count();
        $presentCount = $attendances->where('status', 'present')->count();
        $attendanceRate = $totalClasses > 0 ? round(($presentCount / $totalClasses) * 100, 1) : 'N/A';

        $body = "Dear {$parent->name},\n\n";
        $body .= "Progress Update for {$studentName}:\n\n";
        $body .= "Average Grade: {$avgGrade}\n";
        $body .= "Attendance Rate (30 days): {$attendanceRate}%\n";

        if ($grades->isNotEmpty()) {
            $body .= "\nRecent Grades:\n";
            foreach ($grades->take(5) as $grade) {
                $subjectName = $grade->subject?->name ?? 'Unknown';
                $body .= "- {$subjectName}: {$grade->grade_value}\n";
            }
        }

        $body .= "\nThank you.";

        try {
            Mail::raw($body, function ($message) use ($parent, $studentName) {
                $message->to($parent->email)
                    ->subject("Progress Update: {$studentName}");
            });

            return true;
        } catch (\Throwable $e) {
            Log::error('Failed to send progress update', [
                'parent_id' => $parent->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Send a weekly digest to a parent covering all their students.
     */
    public function sendWeeklyDigest(ParentModel $parent): bool
    {
        $students = $parent->students ?? collect();
        if ($students->isEmpty()) {
            return false;
        }

        $body = "Dear {$parent->name},\n\nWeekly Student Digest:\n\n";

        foreach ($students as $student) {
            $studentName = "{$student->first_name} {$student->last_name}";
            $body .= "--- {$studentName} ---\n";

            $weeklyAttendance = Attendance::where('student_id', $student->id)
                ->where('date', '>=', now()->subDays(7))
                ->get();
            $present = $weeklyAttendance->where('status', 'present')->count();
            $total = $weeklyAttendance->count();
            $body .= "Attendance this week: {$present}/{$total}\n";

            $recentGrades = Grade::where('student_id', $student->id)
                ->where('created_at', '>=', now()->subDays(7))
                ->with('subject')
                ->get();

            if ($recentGrades->isNotEmpty()) {
                $body .= "New grades:\n";
                foreach ($recentGrades as $grade) {
                    $body .= "  - {$grade->subject?->name}: {$grade->grade_value}\n";
                }
            }

            $body .= "\n";
        }

        $body .= "Thank you for staying engaged in your child's education.";

        try {
            Mail::raw($body, function ($message) use ($parent) {
                $message->to($parent->email)
                    ->subject('Weekly Student Digest');
            });

            return true;
        } catch (\Throwable $e) {
            Log::error('Failed to send weekly digest', [
                'parent_id' => $parent->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
