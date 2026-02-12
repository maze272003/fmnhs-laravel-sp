<?php

namespace App\Services;

use App\Models\Grade;
use App\Models\ProgressReport;
use App\Models\ReportSchedule;
use App\Models\Section;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class ReportGenerationService
{
    /**
     * Generate a progress report for a student over a period.
     */
    public function generateProgressReport(Student $student, string $periodStart, string $periodEnd): ProgressReport
    {
        $grades = Grade::where('student_id', $student->id)
            ->whereBetween('created_at', [$periodStart, $periodEnd])
            ->with('subject')
            ->get();

        $attendances = $student->attendances()
            ->whereBetween('date', [$periodStart, $periodEnd])
            ->get();

        $totalClasses = $attendances->count();
        $presentCount = $attendances->where('status', 'present')->count();
        $attendanceRate = $totalClasses > 0 ? round(($presentCount / $totalClasses) * 100, 2) : 0;

        $reportData = [
            'student' => [
                'id' => $student->id,
                'name' => "{$student->first_name} {$student->last_name}",
                'section' => $student->section?->name,
                'lrn' => $student->lrn,
            ],
            'period' => ['start' => $periodStart, 'end' => $periodEnd],
            'grades' => $grades->groupBy('subject.name')->map(fn ($g) => [
                'average' => round($g->avg('grade_value'), 2),
                'grades' => $g->pluck('grade_value')->toArray(),
            ])->toArray(),
            'attendance' => [
                'total_classes' => $totalClasses,
                'present' => $presentCount,
                'absent' => $attendances->where('status', 'absent')->count(),
                'late' => $attendances->where('status', 'late')->count(),
                'rate' => $attendanceRate,
            ],
            'overall_average' => $grades->isNotEmpty() ? round($grades->avg('grade_value'), 2) : null,
        ];

        return ProgressReport::updateOrCreate(
            [
                'student_id' => $student->id,
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
            ],
            [
                'teacher_id' => $student->section?->teacher_id,
                'report_data' => $reportData,
            ]
        );
    }

    /**
     * Generate a PDF for a progress report.
     */
    public function generatePDF(ProgressReport $report): string
    {
        $data = $report->report_data;
        $content = "PROGRESS REPORT\n";
        $content .= str_repeat('=', 40) . "\n";
        $content .= "Student: {$data['student']['name']}\n";
        $content .= "Section: {$data['student']['section']}\n";
        $content .= "Period: {$data['period']['start']} to {$data['period']['end']}\n\n";

        if (!empty($data['grades'])) {
            $content .= "GRADES:\n";
            foreach ($data['grades'] as $subject => $gradeData) {
                $content .= "  {$subject}: {$gradeData['average']}\n";
            }
            $content .= "\n";
        }

        $content .= "ATTENDANCE:\n";
        $content .= "  Total: {$data['attendance']['total_classes']}\n";
        $content .= "  Present: {$data['attendance']['present']}\n";
        $content .= "  Rate: {$data['attendance']['rate']}%\n";

        $path = "reports/progress-{$report->student_id}-{$report->period_start}.txt";
        Storage::disk('local')->put($path, $content);

        $report->update(['pdf_path' => $path]);

        return $path;
    }

    /**
     * Generate a class-wide report for a section.
     */
    public function generateClassReport(Section $section, string $period): array
    {
        $students = $section->students()->with('grades')->get();

        $studentReports = $students->map(function (Student $student) use ($period) {
            $grades = $student->grades()->where('quarter', $period)->get();
            $avgGrade = $grades->isNotEmpty() ? round($grades->avg('grade_value'), 2) : null;

            return [
                'student_id' => $student->id,
                'name' => "{$student->first_name} {$student->last_name}",
                'average_grade' => $avgGrade,
                'subjects_count' => $grades->groupBy('subject_id')->count(),
            ];
        });

        $classAverage = $studentReports->avg('average_grade');

        return [
            'section' => $section->name,
            'grade_level' => $section->grade_level,
            'period' => $period,
            'total_students' => $students->count(),
            'class_average' => $classAverage ? round($classAverage, 2) : null,
            'students' => $studentReports->toArray(),
        ];
    }

    /**
     * Schedule report generation for a teacher.
     */
    public function scheduleReports(Teacher $teacher, string $frequency): ReportSchedule
    {
        $nextRun = match ($frequency) {
            'daily' => now()->addDay(),
            'weekly' => now()->addWeek(),
            'monthly' => now()->addMonth(),
            'quarterly' => now()->addMonths(3),
            default => now()->addWeek(),
        };

        return ReportSchedule::updateOrCreate(
            ['teacher_id' => $teacher->id],
            [
                'frequency' => $frequency,
                'next_run_at' => $nextRun,
                'settings' => ['auto_email' => true],
                'is_active' => true,
            ]
        );
    }

    /**
     * Send a report to recipients via email.
     */
    public function sendReport(ProgressReport $report, array $recipients): bool
    {
        $data = $report->report_data;

        try {
            $studentName = $data['student']['name'] ?? 'Student';
            $period = "{$data['period']['start']} to {$data['period']['end']}";

            Mail::raw(
                "Progress Report for {$studentName}\nPeriod: {$period}\nOverall Average: {$data['overall_average']}",
                function ($message) use ($recipients, $studentName) {
                    $message->to($recipients)
                        ->subject("Progress Report: {$studentName}");
                }
            );

            $report->update(['sent_at' => now()]);

            return true;
        } catch (\Throwable $e) {
            Log::error('Failed to send progress report', [
                'report_id' => $report->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
