<?php

namespace App\Services;

use App\Models\Student;
use App\Models\StudyGoal;
use App\Models\StudySession;
use App\Models\Subject;

class StudyTrackingService
{
    /**
     * Start a study session for a student.
     */
    public function startSession(Student $student, string $type = 'pomodoro', ?Subject $subject = null): StudySession
    {
        return StudySession::create([
            'student_id' => $student->id,
            'subject_id' => $subject?->id,
            'duration_minutes' => 0,
            'session_type' => $type,
            'notes' => null,
            'started_at' => now(),
            'ended_at' => null,
        ]);
    }

    /**
     * End an active study session.
     */
    public function endSession(StudySession $session): StudySession
    {
        $endTime = now();
        $duration = $session->started_at
            ? (int) $session->started_at->diffInMinutes($endTime)
            : 0;

        $session->update([
            'ended_at' => $endTime,
            'duration_minutes' => $duration,
        ]);

        // Update related goals
        $this->updateRelatedGoals($session->student_id, $duration);

        return $session->fresh();
    }

    /**
     * Get study statistics for a student over a period.
     */
    public function getStudyStats(Student $student, string $period = 'weekly'): array
    {
        $startDate = match ($period) {
            'daily' => now()->startOfDay(),
            'weekly' => now()->startOfWeek(),
            'monthly' => now()->startOfMonth(),
            default => now()->startOfWeek(),
        };

        $sessions = StudySession::where('student_id', $student->id)
            ->where('started_at', '>=', $startDate)
            ->whereNotNull('ended_at')
            ->get();

        $totalMinutes = $sessions->sum('duration_minutes');
        $bySubject = $sessions->groupBy('subject_id')
            ->map(fn ($g) => [
                'sessions' => $g->count(),
                'total_minutes' => $g->sum('duration_minutes'),
            ])->toArray();

        return [
            'student_id' => $student->id,
            'period' => $period,
            'total_sessions' => $sessions->count(),
            'total_minutes' => $totalMinutes,
            'total_hours' => round($totalMinutes / 60, 1),
            'average_session_minutes' => $sessions->isNotEmpty()
                ? round($totalMinutes / $sessions->count(), 1)
                : 0,
            'by_subject' => $bySubject,
        ];
    }

    /**
     * Get study goals for a student.
     */
    public function getStudyGoals(Student $student): array
    {
        $goals = StudyGoal::where('student_id', $student->id)
            ->orderByDesc('created_at')
            ->get();

        return [
            'active' => $goals->where('is_completed', false)->values()->toArray(),
            'completed' => $goals->where('is_completed', true)->values()->toArray(),
            'total' => $goals->count(),
        ];
    }

    /**
     * Create a study goal.
     */
    public function createGoal(Student $student, array $data): StudyGoal
    {
        return StudyGoal::create([
            'student_id' => $student->id,
            'title' => $data['title'],
            'target_minutes' => $data['target_minutes'],
            'current_minutes' => 0,
            'period' => $data['period'] ?? 'weekly',
            'is_completed' => false,
            'due_date' => $data['due_date'] ?? now()->endOfWeek(),
        ]);
    }

    /**
     * Update progress on a study goal.
     */
    public function updateGoalProgress(StudyGoal $goal): StudyGoal
    {
        $startDate = match ($goal->period) {
            'daily' => now()->startOfDay(),
            'weekly' => now()->startOfWeek(),
            'monthly' => now()->startOfMonth(),
            default => now()->startOfWeek(),
        };

        $totalMinutes = StudySession::where('student_id', $goal->student_id)
            ->where('started_at', '>=', $startDate)
            ->whereNotNull('ended_at')
            ->sum('duration_minutes');

        $isCompleted = $totalMinutes >= $goal->target_minutes;

        $goal->update([
            'current_minutes' => $totalMinutes,
            'is_completed' => $isCompleted,
        ]);

        return $goal->fresh();
    }

    /**
     * Update all active goals for a student after a session ends.
     */
    protected function updateRelatedGoals(int $studentId, int $addedMinutes): void
    {
        $activeGoals = StudyGoal::where('student_id', $studentId)
            ->where('is_completed', false)
            ->get();

        foreach ($activeGoals as $goal) {
            $this->updateGoalProgress($goal);
        }
    }
}
