<?php

namespace App\Services;

use App\Models\Teacher;
use App\Models\TeacherActivity;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class WorkloadTrackingService
{
    /**
     * Log a teacher activity.
     */
    public function logActivity(
        Teacher $teacher,
        string $type,
        ?int $duration = null,
        ?array $metadata = null
    ): TeacherActivity {
        return TeacherActivity::create([
            'teacher_id' => $teacher->id,
            'activity_type' => $type,
            'description' => $this->getActivityDescription($type),
            'duration_minutes' => $duration,
            'metadata' => $metadata,
            'performed_at' => now(),
        ]);
    }

    /**
     * Get workload metrics for a teacher over a period.
     */
    public function getWorkloadMetrics(Teacher $teacher, string $period = 'weekly'): array
    {
        $startDate = match ($period) {
            'daily' => now()->startOfDay(),
            'weekly' => now()->startOfWeek(),
            'monthly' => now()->startOfMonth(),
            default => now()->startOfWeek(),
        };

        $activities = TeacherActivity::where('teacher_id', $teacher->id)
            ->where('performed_at', '>=', $startDate)
            ->get();

        $totalMinutes = $activities->sum('duration_minutes') ?? 0;
        $byType = $activities->groupBy('activity_type')
            ->map(fn ($g) => [
                'count' => $g->count(),
                'total_minutes' => $g->sum('duration_minutes'),
            ])
            ->toArray();

        return [
            'teacher_id' => $teacher->id,
            'period' => $period,
            'total_activities' => $activities->count(),
            'total_minutes' => $totalMinutes,
            'total_hours' => round($totalMinutes / 60, 1),
            'by_type' => $byType,
        ];
    }

    /**
     * Get workload distribution across all teachers.
     */
    public function getWorkloadDistribution(): array
    {
        $weekStart = now()->startOfWeek();

        $weeklyTotals = TeacherActivity::where('performed_at', '>=', $weekStart)
            ->select('teacher_id', DB::raw('COALESCE(SUM(duration_minutes), 0) as total_minutes'))
            ->groupBy('teacher_id')
            ->pluck('total_minutes', 'teacher_id');

        $distribution = Teacher::select('id', 'first_name', 'last_name')
            ->get()
            ->map(function (Teacher $teacher) use ($weeklyTotals) {
                $weeklyMinutes = $weeklyTotals[$teacher->id] ?? 0;

                return [
                    'teacher_id' => $teacher->id,
                    'name' => "{$teacher->first_name} {$teacher->last_name}",
                    'weekly_minutes' => $weeklyMinutes,
                    'weekly_hours' => round($weeklyMinutes / 60, 1),
                ];
            })->sortByDesc('weekly_minutes')->values();

        return [
            'average_weekly_hours' => round($distribution->avg('weekly_hours') ?? 0, 1),
            'max_weekly_hours' => round($distribution->max('weekly_hours') ?? 0, 1),
            'teachers' => $distribution->toArray(),
        ];
    }

    /**
     * Identify teachers with workload above a threshold.
     */
    public function identifyOverloadedTeachers(): Collection
    {
        $threshold = config('services.workload.weekly_hours_threshold', 40);
        $thresholdMinutes = $threshold * 60;

        return TeacherActivity::where('performed_at', '>=', now()->startOfWeek())
            ->select('teacher_id', DB::raw('COALESCE(SUM(duration_minutes), 0) as total_minutes'))
            ->groupBy('teacher_id')
            ->having('total_minutes', '>', $thresholdMinutes)
            ->get()
            ->map(function ($row) {
                $teacher = Teacher::find($row->teacher_id);

                return [
                    'teacher_id' => $row->teacher_id,
                    'name' => $teacher ? "{$teacher->first_name} {$teacher->last_name}" : 'Unknown',
                    'weekly_hours' => round($row->total_minutes / 60, 1),
                ];
            })->values();
    }

    /**
     * Generate a workload report for a teacher.
     */
    public function generateWorkloadReport(Teacher $teacher): string
    {
        $weekly = $this->getWorkloadMetrics($teacher, 'weekly');
        $monthly = $this->getWorkloadMetrics($teacher, 'monthly');

        $pdf = Pdf::loadView('pdf.workload-report', compact('teacher', 'weekly', 'monthly'));

        $path = "reports/workload-{$teacher->id}-" . now()->format('Ymd') . '.pdf';
        Storage::disk('local')->put($path, $pdf->output());

        return $path;
    }

    /**
     * Get a description for an activity type.
     */
    protected function getActivityDescription(string $type): string
    {
        return match ($type) {
            'grading' => 'Grading student work',
            'lesson_planning' => 'Preparing lesson plans',
            'conference' => 'Conducting video conference',
            'attendance' => 'Taking attendance',
            'communication' => 'Parent/student communication',
            'admin' => 'Administrative tasks',
            default => ucfirst(str_replace('_', ' ', $type)),
        };
    }
}
