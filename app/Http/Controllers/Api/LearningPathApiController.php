<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LearningPath;
use App\Models\Student;
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
            $student = Student::findOrFail($validated['student_id']);
            $progress = $this->adaptiveLearningService->assignPath($student, $path);

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
        $student = Student::findOrFail(Auth::id());

        try {
            $progress = $this->adaptiveLearningService->getProgress($student, $path);

            return response()->json($progress);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Update progress on a learning path (delegates to progress).
     */
    public function updateProgress(LearningPath $path): JsonResponse
    {
        return $this->progress($path);
    }

    /**
     * Get recommended learning paths.
     */
    public function recommend(LearningPath $path): JsonResponse
    {
        $student = Student::findOrFail(Auth::id());

        try {
            $recommendations = $this->adaptiveLearningService->getRecommendations($student);

            return response()->json($recommendations);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
