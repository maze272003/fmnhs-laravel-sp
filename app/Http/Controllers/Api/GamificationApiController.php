<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use App\Models\Student;
use App\Services\GamificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GamificationApiController extends Controller
{
    public function __construct(
        private readonly GamificationService $gamificationService,
    ) {}

    /**
     * Get student's gamification summary.
     */
    public function summary(): JsonResponse
    {
        $student = Auth::user();
        $rank = $this->gamificationService->getStudentRank($student);

        $recentPoints = $student->points()
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'rank' => $rank,
            'recent_points' => $recentPoints,
            'badges' => $student->badges()->get(),
            'achievements' => $student->achievements()->get(),
        ]);
    }

    /**
     * Get leaderboard.
     */
    public function leaderboard(Request $request): JsonResponse
    {
        $gradeLevel = $request->query('grade_level');
        $limit = min($request->query('limit', 10), 100);

        $leaderboard = $this->gamificationService->getLeaderboard($limit, $gradeLevel);

        return response()->json($leaderboard);
    }

    /**
     * Get all available badges.
     */
    public function badges(): JsonResponse
    {
        $badges = Badge::where('is_active', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        return response()->json($badges);
    }

    /**
     * Get student's badges.
     */
    public function studentBadges(): JsonResponse
    {
        $student = Auth::user();
        $badges = $student->badges()->get();

        return response()->json($badges);
    }

    /**
     * Get student's points history.
     */
    public function pointsHistory(Request $request): JsonResponse
    {
        $student = Auth::user();
        $perPage = min($request->query('per_page', 20), 100);

        $points = $student->points()
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json($points);
    }
}
