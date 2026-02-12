<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudyGoal;
use App\Models\StudySession;
use App\Models\Subject;
use App\Services\StudyTrackingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudySessionApiController extends Controller
{
    public function __construct(
        private readonly StudyTrackingService $studyTrackingService,
    ) {}

    /**
     * Start a study session.
     */
    public function start(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['sometimes', 'string'],
            'subject_id' => ['nullable', 'exists:subjects,id'],
        ]);

        $student = Student::findOrFail(Auth::id());
        $subject = ! empty($validated['subject_id']) ? Subject::find($validated['subject_id']) : null;

        try {
            $session = $this->studyTrackingService->startSession(
                $student,
                $validated['type'] ?? 'pomodoro',
                $subject
            );

            return response()->json($session, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * End a study session.
     */
    public function end(StudySession $session): JsonResponse
    {
        try {
            $session = $this->studyTrackingService->endSession($session);

            return response()->json($session);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Get study statistics.
     */
    public function stats(Request $request): JsonResponse
    {
        $student = Student::findOrFail(Auth::id());
        $period = $request->query('period', 'weekly');

        try {
            $stats = $this->studyTrackingService->getStudyStats($student, $period);

            return response()->json($stats);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * List study goals.
     */
    public function goals(): JsonResponse
    {
        $student = Student::findOrFail(Auth::id());

        $goals = $this->studyTrackingService->getStudyGoals($student);

        return response()->json($goals);
    }

    /**
     * Create a study goal.
     */
    public function createGoal(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'target_minutes' => ['nullable', 'integer', 'min:1'],
            'period' => ['nullable', 'string'],
            'due_date' => ['nullable', 'date', 'after:today'],
        ]);

        $student = Student::findOrFail(Auth::id());

        try {
            $goal = $this->studyTrackingService->createGoal($student, $validated);

            return response()->json($goal, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Update a study goal.
     */
    public function updateGoal(StudyGoal $goal): JsonResponse
    {
        try {
            $goal = $this->studyTrackingService->updateGoalProgress($goal);

            return response()->json($goal);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
