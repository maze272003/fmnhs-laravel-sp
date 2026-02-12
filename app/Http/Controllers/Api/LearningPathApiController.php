<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LearningPath;
use App\Services\AdaptiveLearningService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LearningPathApiController extends Controller
{
    public function __construct(
        private readonly AdaptiveLearningService $adaptiveLearningService,
    ) {}

    /**
     * List all learning paths.
     */
    public function index(): JsonResponse
    {
        $paths = LearningPath::orderBy('title')->get();

        return response()->json($paths);
    }

    /**
     * Show a specific learning path with nodes.
     */
    public function show(LearningPath $path): JsonResponse
    {
        $path->load('nodes');

        return response()->json($path);
    }

    /**
     * Assign a learning path to a student.
     */
    public function assign(Request $request, LearningPath $path): JsonResponse
    {
        $validated = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
        ]);

        try {
            $progress = $this->adaptiveLearningService->assignPath(
                $path,
                $validated['student_id']
            );

            return response()->json($progress, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Get student progress on a learning path.
     */
    public function progress(LearningPath $path): JsonResponse
    {
        $user = Auth::user();

        try {
            $progress = $this->adaptiveLearningService->getProgress($path, $user);

            return response()->json($progress);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Get recommended learning paths.
     */
    public function recommend(LearningPath $path): JsonResponse
    {
        $user = Auth::user();

        try {
            $recommendations = $this->adaptiveLearningService->recommend($path, $user);

            return response()->json($recommendations);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
