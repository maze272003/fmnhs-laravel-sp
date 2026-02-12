<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Grade;
use App\Models\InterventionAlert;
use App\Models\Student;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AtRiskDetectionService
{
    /**
     * Detect all at-risk students across the system.
     */
    public function detectAtRiskStudents(): Collection
    {
        $atRisk = collect();

        Student::where('enrollment_status', 'enrolled')
            ->with(['section', 'grades', 'attendances'])
            ->chunk(100, function ($students) use ($atRisk) {
                foreach ($students as $student) {
                    $score = $this->calculateRiskScore($student);

                    if ($score >= 50) {
                        $severity = $score >= 80 ? 'critical' : ($score >= 65 ? 'high' : 'medium');

                        $this->createAlert($student, 'auto_detected', $severity, [
                            'risk_score' => $score,
                            'detected_at' => now()->toIso8601String(),
                        ]);

                        $atRisk->push([
                            'student_id' => $student->id,
                            'name' => "{$student->first_name} {$student->last_name}",
                            'risk_score' => $score,
                            'severity' => $severity,
                        ]);
                    }
                }
            });

        return $atRisk;
    }

    /**
     * Calculate a risk score (0-100) for a student.
     */
    public function calculateRiskScore(Student $student): float
    {
        $score = 0.0;

        // Attendance factor (40% weight)
        $recentAttendance = Attendance::where('student_id', $student->id)
            ->where('date', '>=', now()->subDays(30))
            ->get();
        $totalDays = $recentAttendance->count();
        $absentDays = $recentAttendance->where('status', 'absent')->count();

        if ($totalDays > 0) {
            $absentRate = $absentDays / $totalDays;
            $score += $absentRate * 40;
        }

        // Grade factor (40% weight)
        $recentGrades = Grade::where('student_id', $student->id)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        if ($recentGrades->isNotEmpty()) {
            $avgGrade = $recentGrades->avg('grade_value');
            if ($avgGrade < 75) {
                $score += ((75 - $avgGrade) / 75) * 40;
            }
        } else {
            $score += 20; // No grades is a moderate risk signal
        }

        // Submission factor (20% weight)
        $totalAssignments = $student->section?->assignments()->count() ?? 0;
        $submittedCount = $student->submissions()->count();
        if ($totalAssignments > 0) {
            $missingRate = 1 - ($submittedCount / $totalAssignments);
            $score += $missingRate * 20;
        }

        return min(100, round($score, 1));
    }

    /**
     * Create an intervention alert for a student.
     */
    public function createAlert(
        Student $student,
        string $type,
        string $severity,
        ?array $data = null
    ): InterventionAlert {
        return InterventionAlert::create([
            'student_id' => $student->id,
            'alert_type' => $type,
            'severity' => $severity,
            'description' => $this->generateAlertDescription($type, $severity, $data),
            'data' => $data,
        ]);
    }

    /**
     * Get alerts with optional filters.
     */
    public function getAlerts(array $filters = []): Collection
    {
        $query = InterventionAlert::with('student');

        if (isset($filters['student_id'])) {
            $query->where('student_id', $filters['student_id']);
        }
        if (isset($filters['severity'])) {
            $query->where('severity', $filters['severity']);
        }
        if (isset($filters['alert_type'])) {
            $query->where('alert_type', $filters['alert_type']);
        }
        if (isset($filters['resolved'])) {
            $filters['resolved']
                ? $query->whereNotNull('resolved_at')
                : $query->whereNull('resolved_at');
        }

        return $query->orderByDesc('created_at')->get();
    }

    /**
     * Resolve an intervention alert.
     */
    public function resolveAlert(InterventionAlert $alert, $resolver): InterventionAlert
    {
        $alert->update([
            'resolved_at' => now(),
            'resolved_by_type' => get_class($resolver),
            'resolved_by_id' => $resolver->id,
        ]);

        return $alert->fresh();
    }

    /**
     * Get aggregate alert statistics.
     */
    public function getAlertStatistics(): array
    {
        $alerts = InterventionAlert::query();

        return [
            'total' => (clone $alerts)->count(),
            'unresolved' => (clone $alerts)->whereNull('resolved_at')->count(),
            'resolved' => (clone $alerts)->whereNotNull('resolved_at')->count(),
            'by_severity' => [
                'critical' => (clone $alerts)->where('severity', 'critical')->count(),
                'high' => (clone $alerts)->where('severity', 'high')->count(),
                'medium' => (clone $alerts)->where('severity', 'medium')->count(),
                'low' => (clone $alerts)->where('severity', 'low')->count(),
            ],
            'by_type' => InterventionAlert::select('alert_type', DB::raw('COUNT(*) as count'))
                ->groupBy('alert_type')
                ->pluck('count', 'alert_type')
                ->toArray(),
        ];
    }

    /**
     * Generate a human-readable alert description.
     */
    protected function generateAlertDescription(string $type, string $severity, ?array $data): string
    {
        $riskScore = $data['risk_score'] ?? 'N/A';

        return match ($type) {
            'auto_detected' => "Automatically detected at-risk student with risk score of {$riskScore}. Severity: {$severity}.",
            'low_attendance' => "Student has low attendance rates requiring intervention.",
            'failing_grades' => "Student is showing failing grade patterns.",
            'missing_submissions' => "Student has multiple missing assignment submissions.",
            default => "Intervention alert ({$type}) with {$severity} severity.",
        };
    }
}
