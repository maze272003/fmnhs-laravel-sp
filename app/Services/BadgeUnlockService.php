<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Badge;
use App\Models\Student;
use App\Models\Submission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BadgeUnlockService
{
    public function __construct(
        private readonly GamificationService $gamificationService
    ) {}

    /**
     * Check all unlock conditions and award any earned badges.
     */
    public function checkAndUnlock(Student $student): array
    {
        $unlocked = [];

        if ($this->checkAttendanceStreak($student)) {
            $unlocked[] = 'attendance_streak';
        }

        if ($this->checkQuizMaster($student)) {
            $unlocked[] = 'quiz_master';
        }

        if ($this->checkAssignmentPerfect($student)) {
            $unlocked[] = 'assignment_perfect';
        }

        return [
            'student_id' => $student->id,
            'badges_unlocked' => $unlocked,
            'checked_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Check and award attendance streak badge.
     */
    public function checkAttendanceStreak(Student $student): bool
    {
        $streak = Attendance::where('student_id', $student->id)
            ->where('status', 'present')
            ->where('date', '>=', now()->subDays(10))
            ->distinct('date')
            ->count('date');

        if ($streak >= 10) {
            $badge = Badge::where('slug', 'attendance-streak')->first();
            if ($badge) {
                $this->gamificationService->awardBadge($student, $badge, '10-day attendance streak');
                $this->notifyBadgeUnlock($student, $badge);

                return true;
            }
        }

        return false;
    }

    /**
     * Check and award quiz master badge.
     */
    public function checkQuizMaster(Student $student): bool
    {
        $perfectQuizzes = DB::table('quiz_responses')
            ->select('quiz_id')
            ->where('student_id', $student->id)
            ->where('is_correct', true)
            ->groupBy('quiz_id')
            ->havingRaw('COUNT(*) = (SELECT COUNT(*) FROM quiz_questions WHERE quiz_questions.quiz_id = quiz_responses.quiz_id)')
            ->count();

        if ($perfectQuizzes >= 5) {
            $badge = Badge::where('slug', 'quiz-master')->first();
            if ($badge) {
                $this->gamificationService->awardBadge($student, $badge, 'Achieved 5 perfect quiz scores');
                $this->notifyBadgeUnlock($student, $badge);

                return true;
            }
        }

        return false;
    }

    /**
     * Check and award assignment perfect badge.
     */
    public function checkAssignmentPerfect(Student $student): bool
    {
        $onTimeSubmissions = Submission::where('student_id', $student->id)
            ->whereNotNull('submitted_at')
            ->whereHas('assignment', function ($q) {
                $q->whereColumn('submissions.submitted_at', '<=', 'assignments.deadline');
            })
            ->count();

        if ($onTimeSubmissions >= 20) {
            $badge = Badge::where('slug', 'assignment-perfect')->first();
            if ($badge) {
                $this->gamificationService->awardBadge($student, $badge, '20 on-time submissions');
                $this->notifyBadgeUnlock($student, $badge);

                return true;
            }
        }

        return false;
    }

    /**
     * Get all unlockable badge conditions and current progress.
     */
    public function getUnlockableConditions(): array
    {
        return [
            [
                'badge_slug' => 'attendance-streak',
                'description' => 'Attend 10 consecutive days',
                'type' => 'attendance',
                'threshold' => 10,
            ],
            [
                'badge_slug' => 'quiz-master',
                'description' => 'Get perfect scores on 5 quizzes',
                'type' => 'quiz',
                'threshold' => 5,
            ],
            [
                'badge_slug' => 'assignment-perfect',
                'description' => 'Submit 20 assignments on time',
                'type' => 'assignment',
                'threshold' => 20,
            ],
        ];
    }

    /**
     * Notify a student about a badge unlock.
     */
    public function notifyBadgeUnlock(Student $student, Badge $badge): void
    {
        Log::info('Badge unlocked', [
            'student_id' => $student->id,
            'student_name' => "{$student->first_name} {$student->last_name}",
            'badge' => $badge->name,
            'badge_id' => $badge->id,
        ]);
    }
}
