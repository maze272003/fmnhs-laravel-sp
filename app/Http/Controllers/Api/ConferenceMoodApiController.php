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
            'mood' => ['required', 'string', 'in:happy,neutral,confused,bored,excited'],
            'comment' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $mood = ConferenceMood::create([
                'conference_id' => $conference->id,
                'user_id' => Auth::id(),
                'user_type' => get_class(Auth::user()),
                'mood' => $validated['mood'],
                'comment' => $validated['comment'] ?? null,
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
            ->select('mood', DB::raw('count(*) as count'))
            ->groupBy('mood')
            ->get();

        $total = $aggregate->sum('count');

        return response()->json([
            'total' => $total,
            'moods' => $aggregate,
        ]);
    }
}
