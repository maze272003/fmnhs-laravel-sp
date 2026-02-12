<?php

namespace App\Services;

use App\Models\RecommendedContent;
use App\Models\Student;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ContentRecommendationService
{
    /**
     * Get existing recommendations for a student.
     */
    public function getRecommendations(Student $student): Collection
    {
        return RecommendedContent::where('student_id', $student->id)
            ->where('is_viewed', false)
            ->orderByDesc('relevance_score')
            ->limit(10)
            ->get();
    }

    /**
     * Generate new recommendations for a student based on their performance.
     */
    public function generateRecommendations(Student $student): Collection
    {
        $recommendations = collect();

        // Analyze weak subjects
        $grades = $student->grades()
            ->with('subject')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        $weakSubjects = $grades->groupBy(fn ($g) => $g->subject?->name ?? 'Unknown')
            ->filter(fn ($g) => $g->avg('grade_value') < 80)
            ->keys();

        foreach ($weakSubjects as $subject) {
            $rec = RecommendedContent::create([
                'student_id' => $student->id,
                'title' => "Review Materials: {$subject}",
                'type' => 'review',
                'url' => null,
                'source' => 'system',
                'relevance_score' => 90,
                'is_viewed' => false,
            ]);
            $recommendations->push($rec);

            $rec = RecommendedContent::create([
                'student_id' => $student->id,
                'title' => "Practice Exercises: {$subject}",
                'type' => 'exercise',
                'url' => null,
                'source' => 'system',
                'relevance_score' => 85,
                'is_viewed' => false,
            ]);
            $recommendations->push($rec);
        }

        // Recommend based on quiz performance
        $quizPerformance = DB::table('quiz_responses')
            ->where('student_id', $student->id)
            ->where('is_correct', false)
            ->select('quiz_id', DB::raw('COUNT(*) as wrong_count'))
            ->groupBy('quiz_id')
            ->orderByDesc('wrong_count')
            ->limit(3)
            ->get();

        foreach ($quizPerformance as $quiz) {
            $rec = RecommendedContent::create([
                'student_id' => $student->id,
                'title' => "Quiz Review: Revisit Quiz #{$quiz->quiz_id}",
                'type' => 'quiz_review',
                'url' => null,
                'source' => 'system',
                'relevance_score' => 80,
                'is_viewed' => false,
            ]);
            $recommendations->push($rec);
        }

        return $recommendations;
    }

    /**
     * Record feedback on a recommendation.
     */
    public function recordFeedback(RecommendedContent $recommendation, string $feedback): RecommendedContent
    {
        $recommendation->update([
            'feedback' => $feedback,
            'is_viewed' => true,
        ]);

        return $recommendation->fresh();
    }

    /**
     * Get popular content based on how many students have viewed it.
     */
    public function getPopularContent(?string $subject = null): Collection
    {
        $query = RecommendedContent::select('title', 'type', 'source', DB::raw('COUNT(*) as view_count'))
            ->where('is_viewed', true)
            ->groupBy('title', 'type', 'source')
            ->orderByDesc('view_count')
            ->limit(10);

        if ($subject) {
            $query->where('title', 'like', "%{$subject}%");
        }

        return $query->get();
    }
}
