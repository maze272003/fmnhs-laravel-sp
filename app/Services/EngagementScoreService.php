<?php

namespace App\Services;

use App\Models\ConferenceParticipant;
use App\Models\LearningAnalytic;
use App\Models\Student;
use App\Models\VideoConference;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EngagementScoreService
{
    /**
     * Calculate an engagement score for a student in a conference.
     */
    public function calculateScore(Student $student, VideoConference $conference): float
    {
        $participant = ConferenceParticipant::where('conference_id', $conference->id)
            ->where('actor_id', $student->id)
            ->where('actor_type', 'student')
            ->first();

        if (!$participant) {
            return 0.0;
        }

        $score = 0.0;

        // Duration factor (40 points max) - based on proportion of conference attended
        $conferenceDuration = $conference->started_at && $conference->ended_at
            ? $conference->started_at->diffInSeconds($conference->ended_at)
            : 3600;
        $durationRatio = min(1, ($participant->duration_seconds ?? 0) / max($conferenceDuration, 1));
        $score += $durationRatio * 40;

        // Chat participation (30 points max)
        $messageCount = $conference->messages()
            ->where('sender_type', 'student')
            ->where('sender_id', $student->id)
            ->count();
        $score += min(30, $messageCount * 5);

        // Quiz/poll participation (30 points max)
        $quizResponses = DB::table('quiz_responses')
            ->join('quizzes', 'quiz_responses.quiz_id', '=', 'quizzes.id')
            ->where('quizzes.conference_id', $conference->id)
            ->where('quiz_responses.student_id', $student->id)
            ->count();
        $score += min(30, $quizResponses * 10);

        $finalScore = round(min(100, $score), 1);

        // Record the analytic
        LearningAnalytic::create([
            'student_id' => $student->id,
            'metric_type' => 'engagement_score',
            'metric_value' => $finalScore,
            'context' => ['conference_id' => $conference->id],
            'recorded_at' => now(),
        ]);

        return $finalScore;
    }

    /**
     * Get engagement data for all participants in a conference.
     */
    public function getConferenceEngagement(VideoConference $conference): array
    {
        $participants = ConferenceParticipant::where('conference_id', $conference->id)
            ->where('actor_type', 'student')
            ->get();

        $scores = [];
        foreach ($participants as $participant) {
            $student = Student::find($participant->actor_id);
            if (!$student) {
                continue;
            }

            $scores[] = [
                'student_id' => $student->id,
                'name' => "{$student->first_name} {$student->last_name}",
                'score' => $this->calculateScore($student, $conference),
                'duration_seconds' => $participant->duration_seconds,
            ];
        }

        usort($scores, fn ($a, $b) => $b['score'] <=> $a['score']);

        return [
            'conference_id' => $conference->id,
            'total_participants' => count($scores),
            'average_score' => !empty($scores) ? round(collect($scores)->avg('score'), 1) : 0,
            'participants' => $scores,
        ];
    }

    /**
     * Get overall engagement metrics for a student.
     */
    public function getStudentEngagement(Student $student): array
    {
        $analytics = LearningAnalytic::where('student_id', $student->id)
            ->where('metric_type', 'engagement_score')
            ->orderByDesc('recorded_at')
            ->get();

        return [
            'student_id' => $student->id,
            'average_score' => round($analytics->avg('metric_value') ?? 0, 1),
            'total_sessions' => $analytics->count(),
            'highest_score' => $analytics->max('metric_value') ?? 0,
            'lowest_score' => $analytics->min('metric_value') ?? 0,
            'recent_scores' => $analytics->take(10)->pluck('metric_value')->toArray(),
        ];
    }

    /**
     * Get engagement trends over time for a student.
     */
    public function getEngagementTrends(Student $student): array
    {
        $analytics = LearningAnalytic::where('student_id', $student->id)
            ->where('metric_type', 'engagement_score')
            ->orderBy('recorded_at')
            ->get();

        $weekly = $analytics->groupBy(fn ($a) => $a->recorded_at->startOfWeek()->format('Y-m-d'))
            ->map(fn ($group) => round($group->avg('metric_value'), 1));

        return [
            'student_id' => $student->id,
            'weekly_averages' => $weekly->toArray(),
            'trend' => $this->calculateTrendDirection($weekly->values()->toArray()),
        ];
    }

    /**
     * Generate an engagement report for a conference.
     */
    public function generateEngagementReport(VideoConference $conference): array
    {
        $engagement = $this->getConferenceEngagement($conference);

        $distribution = [
            'high' => 0,
            'medium' => 0,
            'low' => 0,
        ];

        foreach ($engagement['participants'] as $p) {
            if ($p['score'] >= 70) {
                $distribution['high']++;
            } elseif ($p['score'] >= 40) {
                $distribution['medium']++;
            } else {
                $distribution['low']++;
            }
        }

        return [
            'conference' => [
                'id' => $conference->id,
                'title' => $conference->title,
            ],
            'summary' => [
                'total_participants' => $engagement['total_participants'],
                'average_score' => $engagement['average_score'],
                'distribution' => $distribution,
            ],
            'participants' => $engagement['participants'],
        ];
    }

    /**
     * Calculate trend direction from a series of values.
     */
    protected function calculateTrendDirection(array $values): string
    {
        if (count($values) < 2) {
            return 'stable';
        }

        $mid = intdiv(count($values), 2);
        $firstHalf = array_slice($values, 0, $mid);
        $secondHalf = array_slice($values, $mid);

        $firstAvg = array_sum($firstHalf) / max(count($firstHalf), 1);
        $secondAvg = array_sum($secondHalf) / max(count($secondHalf), 1);

        if ($secondAvg > $firstAvg + 5) {
            return 'improving';
        } elseif ($secondAvg < $firstAvg - 5) {
            return 'declining';
        }

        return 'stable';
    }
}
