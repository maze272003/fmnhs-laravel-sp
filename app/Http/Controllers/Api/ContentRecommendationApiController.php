<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RecommendedContent;
use App\Models\Student;
use App\Services\ContentRecommendationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContentRecommendationApiController extends Controller
{
    public function __construct(
        private readonly ContentRecommendationService $recommendationService,
    ) {}

    /**
     * Get content recommendations for the authenticated user.
     */
    public function index(): JsonResponse
    {
        $student = Student::findOrFail(Auth::id());

        try {
            $recommendations = $this->recommendationService->getRecommendations($student);

            return response()->json($recommendations);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Provide feedback on a recommendation.
     */
    public function feedback(Request $request, RecommendedContent $recommendation): JsonResponse
    {
        $validated = $request->validate([
            'feedback' => ['required', 'string', 'in:helpful,not_helpful,skipped'],
        ]);

        try {
            $this->recommendationService->recordFeedback($recommendation, $validated['feedback']);

            return response()->json(['message' => 'Feedback recorded.']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
