<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ConferenceMood;
use App\Models\VideoConference;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ConferenceMoodApiController extends Controller
{
    /**
     * Store a mood entry for a conference.
     */
    public function store(Request $request, VideoConference $conference): JsonResponse
    {
        $validated = $request->validate([
            'mood_type' => ['required', 'string'],
            'value' => ['required', 'numeric'],
        ]);

        try {
            $student = Auth::user();

            $mood = ConferenceMood::create([
                'conference_id' => $conference->id,
                'student_id' => $student->id,
                'mood_type' => $validated['mood_type'],
                'value' => $validated['value'],
            ]);

            return response()->json($mood, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * List mood entries for a conference.
     */
    public function index(VideoConference $conference): JsonResponse
    {
        $moods = ConferenceMood::where('conference_id', $conference->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($moods);
    }

    /**
     * Get aggregated mood data for a conference.
     */
    public function aggregate(VideoConference $conference): JsonResponse
    {
        $aggregate = ConferenceMood::where('conference_id', $conference->id)
            ->select('mood_type', DB::raw('count(*) as count'), DB::raw('avg(value) as avg_value'))
            ->groupBy('mood_type')
            ->get();

        $total = $aggregate->sum('count');

        return response()->json([
            'total' => $total,
            'moods' => $aggregate,
        ]);
    }
}
