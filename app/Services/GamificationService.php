<?php

namespace App\Services;

use App\Models\Achievement;
use App\Models\Badge;
use App\Models\Student;
use App\Models\StudentPoint;
use Illuminate\Support\Facades\DB;

class GamificationService
{
    /**
     * Award points to a student.
     */
    public function awardPoints(
        Student $student,
        int $points,
        string $sourceType,
        ?int $sourceId,
        string $reason,
        ?string $details = null
    ): StudentPoint {
        $pointRecord = StudentPoint::create([
            'student_id' => $student->id,
            'source_type' => $sourceType,
            'source_id' => $sourceId,
            'points' => $points,
            'reason' => $reason,
            'details' => $details,
        ]);

        // Check if this triggers any badge or achievement unlocks
        $this->checkBadgeUnlocks($student);
        $this->checkAchievementCompletions($student);

        return $pointRecord;
    }

    /**
     * Award a badge to a student.
     */
    public function awardBadge(Student $student, Badge $badge, ?string $note = null): void
    {
        // Check if student already has this badge
        if ($student->badges()->where('badge_id', $badge->id)->exists()) {
            return;
        }

        $student->badges()->attach($badge->id, [
            'earned_at' => now(),
            'note' => $note,
        ]);

        // Award points if badge has points value
        if ($badge->points_value > 0) {
            $this->awardPoints(
                $student,
                $badge->points_value,
                'badge',
                $badge->id,
                "Earned badge: {$badge->name}"
            );
        }
    }

    /**
     * Complete an achievement for a student.
     */
    public function completeAchievement(
        Student $student,
        Achievement $achievement,
        ?array $completionData = null
    ): void {
        $existing = DB::table('student_achievements')
            ->where('student_id', $student->id)
            ->where('achievement_id', $achievement->id)
            ->first();

        if ($existing && !$achievement->is_repeatable) {
            return; // Already completed and not repeatable
        }

        if ($existing && $achievement->is_repeatable) {
            // Increment completion count
            DB::table('student_achievements')
                ->where('id', $existing->id)
                ->increment('completion_count');
        } else {
            // First time completion
            $student->achievements()->attach($achievement->id, [
                'completed_at' => now(),
                'completion_count' => 1,
                'completion_data' => $completionData,
            ]);
        }

        // Award points
        if ($achievement->points_reward > 0) {
            $this->awardPoints(
                $student,
                $achievement->points_reward,
                'achievement',
                $achievement->id,
                "Completed achievement: {$achievement->name}"
            );
        }

        // Award associated badge
        if ($achievement->badge_id) {
            $badge = Badge::find($achievement->badge_id);
            if ($badge) {
                $this->awardBadge($student, $badge, "Earned through achievement: {$achievement->name}");
            }
        }
    }

    /**
     * Get student leaderboard.
     */
    public function getLeaderboard(int $limit = 10, ?int $gradeLevel = null): array
    {
        $query = DB::table('student_points')
            ->select('student_id', DB::raw('SUM(points) as total_points'))
            ->groupBy('student_id')
            ->orderByDesc('total_points')
            ->limit($limit);

        if ($gradeLevel) {
            $query->join('students', 'student_points.student_id', '=', 'students.id')
                ->join('sections', 'students.section_id', '=', 'sections.id')
                ->where('sections.grade_level', $gradeLevel);
        }

        return $query->get()
            ->map(function ($item) {
                $student = Student::find($item->student_id);
                return [
                    'student_id' => $item->student_id,
                    'student_name' => $student ? "{$student->first_name} {$student->last_name}" : 'Unknown',
                    'total_points' => $item->total_points,
                    'badges_count' => $student ? $student->badges()->count() : 0,
                ];
            })
            ->toArray();
    }

    /**
     * Get student rank.
     */
    public function getStudentRank(Student $student): array
    {
        $totalPoints = $student->points()->sum('points');
        
        $rank = DB::table('student_points')
            ->select('student_id', DB::raw('SUM(points) as total_points'))
            ->groupBy('student_id')
            ->having('total_points', '>', $totalPoints)
            ->count() + 1;

        return [
            'rank' => $rank,
            'total_points' => $totalPoints,
            'badges_earned' => $student->badges()->count(),
            'achievements_completed' => $student->achievements()->count(),
        ];
    }

    /**
     * Award quiz completion points.
     */
    public function awardQuizPoints(Student $student, int $quizId, int $score, int $maxScore): void
    {
        $percentage = $maxScore > 0 ? ($score / $maxScore) * 100 : 0;
        
        // Base points
        $points = (int) ($percentage / 10); // 10 points per 10%
        
        // Bonus for perfect score
        if ($percentage >= 100) {
            $points += 5;
        } elseif ($percentage >= 90) {
            $points += 3;
        } elseif ($percentage >= 80) {
            $points += 1;
        }

        $this->awardPoints(
            $student,
            $points,
            'quiz',
            $quizId,
            "Quiz completed with {$percentage}% score"
        );
    }

    /**
     * Award attendance points.
     */
    public function awardAttendancePoints(Student $student, int $attendanceId): void
    {
        $this->awardPoints(
            $student,
            2,
            'attendance',
            $attendanceId,
            'Attendance marked'
        );

        // Check for attendance streak
        $this->checkAttendanceStreak($student);
    }

    /**
     * Award participation points (e.g., joining a conference).
     */
    public function awardParticipationPoints(Student $student, int $conferenceId, int $durationMinutes): void
    {
        // Award points based on participation duration
        $points = min(10, max(1, (int) ($durationMinutes / 10))); // 1 point per 10 minutes, max 10

        $this->awardPoints(
            $student,
            $points,
            'conference_participation',
            $conferenceId,
            "Participated in conference for {$durationMinutes} minutes"
        );
    }

    /**
     * Check if student unlocked any badges based on criteria.
     */
    protected function checkBadgeUnlocks(Student $student): void
    {
        $badges = Badge::where('is_active', true)->get();

        foreach ($badges as $badge) {
            $criteria = $badge->unlock_criteria;
            
            if ($this->evaluateBadgeCriteria($student, $criteria)) {
                $this->awardBadge($student, $badge);
            }
        }
    }

    /**
     * Check if student completed any achievements.
     */
    protected function checkAchievementCompletions(Student $student): void
    {
        $achievements = Achievement::where('is_active', true)->get();

        foreach ($achievements as $achievement) {
            if ($this->evaluateAchievementRequirements($student, $achievement->requirements)) {
                $this->completeAchievement($student, $achievement);
            }
        }
    }

    /**
     * Evaluate badge unlock criteria.
     */
    protected function evaluateBadgeCriteria(Student $student, array $criteria): bool
    {
        // Example criteria format:
        // ['type' => 'points', 'threshold' => 100]
        // ['type' => 'badges_count', 'threshold' => 5]
        
        if (!isset($criteria['type'])) {
            return false;
        }

        switch ($criteria['type']) {
            case 'points':
                $totalPoints = $student->points()->sum('points');
                return $totalPoints >= ($criteria['threshold'] ?? 0);
                
            case 'badges_count':
                $badgesCount = $student->badges()->count();
                return $badgesCount >= ($criteria['threshold'] ?? 0);
                
            case 'quiz_perfect_scores':
                // Count perfect quiz scores
                $perfectScores = DB::table('quiz_responses')
                    ->where('student_id', $student->id)
                    ->where('is_correct', true)
                    ->distinct('quiz_id')
                    ->count();
                return $perfectScores >= ($criteria['threshold'] ?? 0);
                
            default:
                return false;
        }
    }

    /**
     * Evaluate achievement requirements.
     */
    protected function evaluateAchievementRequirements(Student $student, array $requirements): bool
    {
        // Similar to badge criteria but for achievements
        return $this->evaluateBadgeCriteria($student, $requirements);
    }

    /**
     * Check for attendance streak and award bonus.
     */
    protected function checkAttendanceStreak(Student $student): void
    {
        // Get last 7 days of attendance
        $recentAttendance = DB::table('attendances')
            ->where('student_id', $student->id)
            ->where('status', 'present')
            ->where('date', '>=', now()->subDays(7))
            ->count();

        if ($recentAttendance >= 5) {
            $this->awardPoints(
                $student,
                10,
                'attendance_streak',
                null,
                '5-day attendance streak bonus'
            );
        }

        if ($recentAttendance >= 7) {
            $this->awardPoints(
                $student,
                20,
                'attendance_streak',
                null,
                'Perfect week attendance bonus'
            );
        }
    }
}
