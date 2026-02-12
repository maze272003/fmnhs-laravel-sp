<?php

namespace App\Services;

use App\Models\Teacher;
use App\Models\TeacherActivity;
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
        $teachers = Teacher::all();

        $distribution = $teachers->map(function (Teacher $teacher) {
            $weeklyMinutes = TeacherActivity::where('teacher_id', $teacher->id)
                ->where('performed_at', '>=', now()->startOfWeek())
                ->sum('duration_minutes') ?? 0;

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

        $teachers = Teacher::all();

        return $teachers->filter(function (Teacher $teacher) use ($threshold) {
            $weeklyMinutes = TeacherActivity::where('teacher_id', $teacher->id)
                ->where('performed_at', '>=', now()->startOfWeek())
                ->sum('duration_minutes') ?? 0;

            return ($weeklyMinutes / 60) > $threshold;
        })->map(function (Teacher $teacher) {
            $weeklyMinutes = TeacherActivity::where('teacher_id', $teacher->id)
                ->where('performed_at', '>=', now()->startOfWeek())
                ->sum('duration_minutes') ?? 0;

            return [
                'teacher_id' => $teacher->id,
                'name' => "{$teacher->first_name} {$teacher->last_name}",
                'weekly_hours' => round($weeklyMinutes / 60, 1),
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

        $content = "WORKLOAD REPORT\n";
        $content .= str_repeat('=', 40) . "\n";
        $content .= "Teacher: {$teacher->first_name} {$teacher->last_name}\n";
        $content .= "Generated: " . now()->format('Y-m-d H:i') . "\n\n";

        $content .= "WEEKLY SUMMARY:\n";
        $content .= "  Total Activities: {$weekly['total_activities']}\n";
        $content .= "  Total Hours: {$weekly['total_hours']}\n";
        foreach ($weekly['by_type'] as $type => $data) {
            $content .= "  {$type}: {$data['count']} activities ({$data['total_minutes']} min)\n";
        }

        $content .= "\nMONTHLY SUMMARY:\n";
        $content .= "  Total Activities: {$monthly['total_activities']}\n";
        $content .= "  Total Hours: {$monthly['total_hours']}\n";

        $path = "reports/workload-{$teacher->id}-" . now()->format('Ymd') . '.txt';
        Storage::disk('local')->put($path, $content);

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
