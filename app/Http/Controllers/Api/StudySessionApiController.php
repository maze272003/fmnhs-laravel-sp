<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StudyGoal;
use App\Models\StudySession;
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
            'subject' => ['nullable', 'string', 'max:255'],
            'goal_id' => ['nullable', 'exists:study_goals,id'],
        ]);

        $user = Auth::user();

        try {
            $session = $this->studyTrackingService->startSession($user, $validated);

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
    public function stats(): JsonResponse
    {
        $user = Auth::user();

        try {
            $stats = $this->studyTrackingService->getStats($user);

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
        $user = Auth::user();

        $goals = StudyGoal::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($goals);
    }

    /**
     * Create a study goal.
     */
    public function createGoal(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'target_hours' => ['nullable', 'numeric', 'min:0.5'],
            'target_date' => ['nullable', 'date', 'after:today'],
        ]);

        $user = Auth::user();

        try {
            $goal = $this->studyTrackingService->createGoal($user, $validated);

            return response()->json($goal, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Update a study goal.
     */
    public function updateGoal(Request $request, StudyGoal $goal): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'target_hours' => ['nullable', 'numeric', 'min:0.5'],
            'target_date' => ['nullable', 'date'],
            'status' => ['sometimes', 'string', 'in:active,completed,abandoned'],
        ]);

        try {
            $goal = $this->studyTrackingService->updateGoal($goal, $validated);

            return response()->json($goal);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
