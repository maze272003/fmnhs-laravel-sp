<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Student;
use App\Services\GameEngineService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GameApiController extends Controller
{
    public function __construct(
        private readonly GameEngineService $gameEngineService,
    ) {}

    /**
     * Create a new game.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'string'],
            'settings' => ['nullable', 'array'],
            'conference_id' => ['nullable', 'exists:video_conferences,id'],
        ]);

        try {
            $conference = null;
            if (! empty($validated['conference_id'])) {
                $conference = \App\Models\VideoConference::find($validated['conference_id']);
            }

            $game = $this->gameEngineService->createGame(
                $validated['type'],
                $validated['settings'] ?? [],
                $conference
            );

            return response()->json($game, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Start a game.
     */
    public function start(Game $game): JsonResponse
    {
        try {
            $game = $this->gameEngineService->startGame($game);

            return response()->json($game);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * End a game.
     */
    public function end(Game $game): JsonResponse
    {
        try {
            $game = $this->gameEngineService->endGame($game);

            return response()->json($game);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Join a game.
     */
    public function join(Game $game): JsonResponse
    {
        $student = Student::findOrFail(Auth::guard('student')->id());

        try {
            $session = $this->gameEngineService->joinGame($game, $student);

            return response()->json($session, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Submit an answer in a game.
     */
    public function submitAnswer(Request $request, Game $game): JsonResponse
    {
        $validated = $request->validate([
            'question_id' => ['required', 'integer'],
            'answer' => ['required'],
            'time_taken' => ['nullable', 'integer'],
        ]);

        $student = Student::findOrFail(Auth::guard('student')->id());

        try {
            $result = $this->gameEngineService->submitAnswer($game, $student, $validated);

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Submit a score in a game (delegates to submitAnswer).
     */
    public function submitScore(Request $request, Game $game): JsonResponse
    {
        return $this->submitAnswer($request, $game);
    }

    /**
     * Get game leaderboard.
     */
    public function leaderboard(Game $game): JsonResponse
    {
        $leaderboard = $this->gameEngineService->getLeaderboard($game);

        return response()->json($leaderboard);
    }

    /**
     * Get current game state.
     */
    public function state(Game $game): JsonResponse
    {
        $state = $this->gameEngineService->getGameState($game);

        return response()->json($state);
    }
}
