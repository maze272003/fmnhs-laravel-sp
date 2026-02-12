<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Grade;
use App\Models\LearningAnalytic;
use App\Models\Section;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AnalyticsAggregationService
{
    /**
     * Get comprehensive performance data for a student.
     */
    public function getStudentPerformance(Student $student): array
    {
        $grades = Grade::where('student_id', $student->id)
            ->with('subject')
            ->get();

        $attendances = Attendance::where('student_id', $student->id)->get();
        $totalClasses = $attendances->count();
        $presentCount = $attendances->where('status', 'present')->count();

        return [
            'student_id' => $student->id,
            'name' => "{$student->first_name} {$student->last_name}",
            'overall_average' => $grades->isNotEmpty() ? round($grades->avg('grade_value'), 2) : null,
            'subject_averages' => $grades->groupBy(fn ($g) => $g->subject?->name ?? 'Unknown')
                ->map(fn ($g) => round($g->avg('grade_value'), 2))
                ->toArray(),
            'attendance_rate' => $totalClasses > 0 ? round(($presentCount / $totalClasses) * 100, 2) : 0,
            'total_points' => $student->points()->sum('points'),
            'badges_count' => $student->badges()->count(),
        ];
    }

    /**
     * Get performance metrics for an entire class section.
     */
    public function getClassPerformance(Section $section): array
    {
        $students = $section->students()->get();
        $studentIds = $students->pluck('id');

        $grades = Grade::whereIn('student_id', $studentIds)->get();
        $classAverage = $grades->isNotEmpty() ? round($grades->avg('grade_value'), 2) : null;

        $topStudents = $grades->groupBy('student_id')
            ->map(fn ($g) => $g->avg('grade_value'))
            ->sortDesc()
            ->take(5);

        return [
            'section_id' => $section->id,
            'section_name' => $section->name,
            'total_students' => $students->count(),
            'class_average' => $classAverage,
            'highest_average' => $grades->isNotEmpty() ? round($grades->max('grade_value'), 2) : null,
            'lowest_average' => $grades->isNotEmpty() ? round($grades->min('grade_value'), 2) : null,
            'top_students' => $topStudents->map(fn ($avg, $id) => [
                'student_id' => $id,
                'average' => round($avg, 2),
            ])->values()->toArray(),
        ];
    }

    /**
     * Get attendance trends for a section.
     */
    public function getAttendanceTrends(Section $section): array
    {
        $studentIds = $section->students()->pluck('id');

        $attendances = Attendance::whereIn('student_id', $studentIds)
            ->where('date', '>=', now()->subDays(30))
            ->get();

        $dailyRates = $attendances->groupBy('date')
            ->map(function ($dayRecords) {
                $total = $dayRecords->count();
                $present = $dayRecords->where('status', 'present')->count();

                return $total > 0 ? round(($present / $total) * 100, 1) : 0;
            })
            ->sortKeys();

        return [
            'section_id' => $section->id,
            'period' => 'last_30_days',
            'daily_rates' => $dailyRates->toArray(),
            'average_rate' => round($dailyRates->avg() ?? 0, 1),
        ];
    }

    /**
     * Get grade trends for a student.
     */
    public function getGradeTrends(Student $student): array
    {
        $grades = Grade::where('student_id', $student->id)
            ->with('subject')
            ->orderBy('quarter')
            ->get();

        $quarterlyAverages = $grades->groupBy('quarter')
            ->map(fn ($g) => round($g->avg('grade_value'), 2))
            ->toArray();

        $subjectTrends = $grades->groupBy(fn ($g) => $g->subject?->name ?? 'Unknown')
            ->map(fn ($g) => $g->groupBy('quarter')
                ->map(fn ($qg) => round($qg->avg('grade_value'), 2))
                ->toArray()
            )->toArray();

        return [
            'student_id' => $student->id,
            'quarterly_averages' => $quarterlyAverages,
            'subject_trends' => $subjectTrends,
            'trend_direction' => $this->getTrendDirection($quarterlyAverages),
        ];
    }

    /**
     * Get engagement metrics for a student.
     */
    public function getEngagementMetrics(Student $student): array
    {
        $analytics = LearningAnalytic::where('student_id', $student->id)
            ->orderByDesc('recorded_at')
            ->get();

        $byType = $analytics->groupBy('metric_type')
            ->map(fn ($g) => [
                'average' => round($g->avg('metric_value'), 2),
                'latest' => $g->first()?->metric_value,
                'count' => $g->count(),
            ])->toArray();

        return [
            'student_id' => $student->id,
            'total_records' => $analytics->count(),
            'metrics_by_type' => $byType,
        ];
    }

    /**
     * Export report data in the specified format.
     */
    public function exportReport(array $data, string $format = 'pdf'): string
    {
        $content = json_encode($data, JSON_PRETTY_PRINT);
        $extension = $format === 'csv' ? 'csv' : 'json';
        $path = "analytics/report-" . now()->format('Ymd-His') . ".{$extension}";

        if ($format === 'csv' && isset($data[0]) && is_array($data[0])) {
            $headers = array_keys($data[0]);
            $csv = implode(',', $headers) . "\n";
            foreach ($data as $row) {
                $csv .= implode(',', array_map(fn ($v) => '"' . str_replace('"', '""', (string) $v) . '"', $row)) . "\n";
            }
            $content = $csv;
        }

        Storage::disk('local')->put($path, $content);

        return $path;
    }

    /**
     * Determine trend direction from quarterly averages.
     */
    protected function getTrendDirection(array $values): string
    {
        $vals = array_values($values);
        if (count($vals) < 2) {
            return 'insufficient_data';
        }

        $last = end($vals);
        $prev = prev($vals);

        if ($last > $prev + 2) {
            return 'improving';
        } elseif ($last < $prev - 2) {
            return 'declining';
        }

        return 'stable';
    }
}
