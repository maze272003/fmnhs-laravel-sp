<?php

namespace App\Services;

use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizResponse;
use App\Models\Student;
use App\Models\VideoConference;
use Illuminate\Support\Facades\DB;

class QuizService
{
    /**
     * Create a new quiz.
     */
    public function createQuiz(array $data): Quiz
    {
        return Quiz::create($data);
    }

    /**
     * Add a question to a quiz.
     */
    public function addQuestion(Quiz $quiz, array $data): QuizQuestion
    {
        $data['quiz_id'] = $quiz->id;
        
        // Auto-set order if not provided
        if (!isset($data['order'])) {
            $data['order'] = $quiz->questions()->max('order') + 1;
        }

        return QuizQuestion::create($data);
    }

    /**
     * Start a quiz (activate it).
     */
    public function startQuiz(Quiz $quiz): Quiz
    {
        $quiz->update([
            'status' => 'active',
            'started_at' => now(),
        ]);

        return $quiz->fresh();
    }

    /**
     * End a quiz (complete it).
     */
    public function endQuiz(Quiz $quiz): Quiz
    {
        $quiz->update([
            'status' => 'completed',
            'ended_at' => now(),
        ]);

        return $quiz->fresh();
    }

    /**
     * Submit a student's response to a question.
     */
    public function submitResponse(Quiz $quiz, QuizQuestion $question, Student $student, array $selectedAnswers, ?int $timeTaken = null): QuizResponse
    {
        // Check if correct (only for quiz type, not for polls)
        $isCorrect = null;
        $pointsEarned = 0;

        if ($quiz->type !== 'poll' && $question->correct_answers !== null) {
            $isCorrect = $this->checkAnswer($question, $selectedAnswers);
            $pointsEarned = $isCorrect ? $question->points : 0;
        }

        // Update or create response
        return QuizResponse::updateOrCreate(
            [
                'question_id' => $question->id,
                'student_id' => $student->id,
            ],
            [
                'quiz_id' => $quiz->id,
                'selected_answers' => $selectedAnswers,
                'is_correct' => $isCorrect,
                'points_earned' => $pointsEarned,
                'time_taken' => $timeTaken,
            ]
        );
    }

    /**
     * Check if the answer is correct.
     */
    protected function checkAnswer(QuizQuestion $question, array $selectedAnswers): bool
    {
        $correctAnswers = $question->correct_answers ?? [];
        
        // Sort both arrays to compare them
        sort($selectedAnswers);
        sort($correctAnswers);

        return $selectedAnswers === $correctAnswers;
    }

    /**
     * Get quiz leaderboard.
     */
    public function getLeaderboard(Quiz $quiz, int $limit = 10): array
    {
        return DB::table('quiz_responses')
            ->select('student_id', DB::raw('SUM(points_earned) as total_points'))
            ->where('quiz_id', $quiz->id)
            ->groupBy('student_id')
            ->orderByDesc('total_points')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                $student = Student::find($item->student_id);
                return [
                    'student_id' => $item->student_id,
                    'student_name' => $student ? "{$student->first_name} {$student->last_name}" : 'Unknown',
                    'total_points' => $item->total_points,
                ];
            })
            ->toArray();
    }

    /**
     * Get quiz results for a specific student.
     */
    public function getStudentResults(Quiz $quiz, Student $student): array
    {
        $responses = QuizResponse::where('quiz_id', $quiz->id)
            ->where('student_id', $student->id)
            ->get();

        $totalPoints = $responses->sum('points_earned');
        $maxPoints = $quiz->questions()->sum('points');
        $percentage = $maxPoints > 0 ? ($totalPoints / $maxPoints) * 100 : 0;

        return [
            'total_points' => $totalPoints,
            'max_points' => $maxPoints,
            'percentage' => round($percentage, 2),
            'passed' => $quiz->passing_score ? $percentage >= $quiz->passing_score : null,
            'responses' => $responses,
        ];
    }

    /**
     * Get quiz statistics.
     */
    public function getQuizStatistics(Quiz $quiz): array
    {
        $totalQuestions = $quiz->questions()->count();
        $totalResponses = QuizResponse::where('quiz_id', $quiz->id)
            ->distinct('student_id')
            ->count('student_id');

        $averageScore = DB::table('quiz_responses')
            ->where('quiz_id', $quiz->id)
            ->groupBy('student_id')
            ->select(DB::raw('SUM(points_earned) as total'))
            ->get()
            ->avg('total');

        return [
            'total_questions' => $totalQuestions,
            'total_participants' => $totalResponses,
            'average_score' => round($averageScore ?? 0, 2),
        ];
    }

    /**
     * Get real-time results for a question.
     */
    public function getQuestionResults(QuizQuestion $question): array
    {
        $responses = QuizResponse::where('question_id', $question->id)->get();
        
        $optionCounts = [];
        foreach ($question->options as $index => $option) {
            $optionCounts[$index] = 0;
        }

        foreach ($responses as $response) {
            foreach ($response->selected_answers as $answer) {
                if (isset($optionCounts[$answer])) {
                    $optionCounts[$answer]++;
                }
            }
        }

        return [
            'total_responses' => $responses->count(),
            'option_counts' => $optionCounts,
            'correct_percentage' => $responses->where('is_correct', true)->count() / max($responses->count(), 1) * 100,
        ];
    }
}
