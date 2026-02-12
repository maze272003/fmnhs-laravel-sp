<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InterventionAlert;
use App\Services\AtRiskDetectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InterventionAlertApiController extends Controller
{
    public function __construct(
        private readonly AtRiskDetectionService $atRiskService,
    ) {}

    /**
     * List intervention alerts.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min($request->query('per_page', 20), 100);

        $alerts = InterventionAlert::with('student')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json($alerts);
    }

    /**
     * Show a specific alert.
     */
    public function show(InterventionAlert $alert): JsonResponse
    {
        $alert->load('student');

        return response()->json($alert);
    }

    /**
     * Resolve an alert.
     */
    public function resolve(Request $request, InterventionAlert $alert): JsonResponse
    {
        $validated = $request->validate([
            'resolution_notes' => ['nullable', 'string'],
            'action_taken' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $alert = $this->atRiskService->resolveAlert($alert, $validated);

            return response()->json($alert);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Get alert statistics.
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = $this->atRiskService->getStatistics();

            return response()->json($stats);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
