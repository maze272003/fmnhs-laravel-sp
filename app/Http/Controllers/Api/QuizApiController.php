<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\Student;
use App\Models\VideoConference;
use App\Services\QuizService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuizApiController extends Controller
{
    public function __construct(
        private readonly QuizService $quizService,
    ) {}

    /**
     * Get all quizzes for a conference.
     */
    public function index(VideoConference $conference): JsonResponse
    {
        $quizzes = Quiz::where('conference_id', $conference->id)
            ->with('questions')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($quizzes);
    }

    /**
     * Create a new quiz.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'conference_id' => ['nullable', 'exists:video_conferences,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'in:quiz,poll,survey'],
            'time_limit' => ['nullable', 'integer', 'min:10'],
            'show_correct_answers' => ['boolean'],
            'show_leaderboard' => ['boolean'],
            'randomize_questions' => ['boolean'],
            'randomize_options' => ['boolean'],
            'passing_score' => ['nullable', 'integer', 'min:0', 'max:100'],
        ]);

        $teacher = Auth::user(); // Teacher is the authenticated model itself
        $validated['teacher_id'] = $teacher->id;

        $quiz = $this->quizService->createQuiz($validated);

        return response()->json($quiz, 201);
    }

    /**
     * Get a specific quiz.
     */
    public function show(Quiz $quiz): JsonResponse
    {
        $quiz->load('questions', 'teacher');

        return response()->json($quiz);
    }

    /**
     * Update a quiz.
     */
    public function update(Request $request, Quiz $quiz): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['sometimes', 'in:quiz,poll,survey'],
            'time_limit' => ['nullable', 'integer', 'min:10'],
            'show_correct_answers' => ['boolean'],
            'show_leaderboard' => ['boolean'],
            'randomize_questions' => ['boolean'],
            'randomize_options' => ['boolean'],
            'passing_score' => ['nullable', 'integer', 'min:0', 'max:100'],
        ]);

        $quiz->update($validated);

        return response()->json($quiz);
    }

    /**
     * Delete a quiz.
     */
    public function destroy(Quiz $quiz): JsonResponse
    {
        $quiz->delete();

        return response()->json(['message' => 'Quiz deleted successfully']);
    }

    /**
     * Add a question to a quiz.
     */
    public function addQuestion(Request $request, Quiz $quiz): JsonResponse
    {
        $validated = $request->validate([
            'question' => ['required', 'string'],
            'type' => ['required', 'in:multiple_choice,true_false,poll'],
            'options' => ['required', 'array', 'min:2'],
            'options.*' => ['required', 'string'],
            'correct_answers' => ['nullable', 'array', 'required_unless:type,poll'],
            'correct_answers.*' => ['integer'],
            'points' => ['integer', 'min:0'],
            'time_limit' => ['nullable', 'integer', 'min:10'],
            'explanation' => ['nullable', 'string'],
        ]);

        // Ensure poll questions don't have correct answers
        if ($validated['type'] === 'poll') {
            $validated['correct_answers'] = null;
            $validated['points'] = 0;
        }

        $question = $this->quizService->addQuestion($quiz, $validated);

        return response()->json($question, 201);
    }

    /**
     * Start a quiz.
     */
    public function start(Quiz $quiz): JsonResponse
    {
        $quiz = $this->quizService->startQuiz($quiz);

        return response()->json($quiz);
    }

    /**
     * End a quiz.
     */
    public function end(Quiz $quiz): JsonResponse
    {
        $quiz = $this->quizService->endQuiz($quiz);

        return response()->json($quiz);
    }

    /**
     * Submit a response to a question.
     */
    public function submitResponse(Request $request, Quiz $quiz, QuizQuestion $question): JsonResponse
    {
        $validated = $request->validate([
            'selected_answers' => ['required', 'array'],
            'selected_answers.*' => ['integer'],
            'time_taken' => ['nullable', 'integer'],
        ]);

        $student = Auth::user(); // Student is the authenticated model itself

        $response = $this->quizService->submitResponse(
            $quiz,
            $question,
            $student,
            $validated['selected_answers'],
            $validated['time_taken'] ?? null
        );

        return response()->json($response, 201);
    }

    /**
     * Get quiz leaderboard.
     */
    public function leaderboard(Quiz $quiz): JsonResponse
    {
        $leaderboard = $this->quizService->getLeaderboard($quiz);

        return response()->json($leaderboard);
    }

    /**
     * Get student results.
     */
    public function results(Quiz $quiz): JsonResponse
    {
        $student = Auth::user(); // Student is the authenticated model itself
        $results = $this->quizService->getStudentResults($quiz, $student);

        return response()->json($results);
    }

    /**
     * Get quiz statistics (for teachers).
     */
    public function statistics(Quiz $quiz): JsonResponse
    {
        $statistics = $this->quizService->getQuizStatistics($quiz);

        return response()->json($statistics);
    }

    /**
     * Get real-time results for a question.
     */
    public function questionResults(QuizQuestion $question): JsonResponse
    {
        $results = $this->quizService->getQuestionResults($question);

        return response()->json($results);
    }
}
