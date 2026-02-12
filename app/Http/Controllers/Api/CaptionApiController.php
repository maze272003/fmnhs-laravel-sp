<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Caption;
use App\Models\VideoConference;
use App\Services\CaptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CaptionApiController extends Controller
{
    public function __construct(
        private readonly CaptionService $captionService,
    ) {}

    /**
     * Store a new caption entry.
     */
    public function store(Request $request, VideoConference $conference): JsonResponse
    {
        $validated = $request->validate([
            'text' => ['required', 'string'],
            'speaker' => ['nullable', 'string', 'max:255'],
            'language' => ['sometimes', 'string', 'max:10'],
            'timestamp' => ['nullable', 'numeric'],
        ]);

        try {
            $caption = $this->captionService->storeCaption($conference, $validated);

            return response()->json($caption, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * List captions for a conference.
     */
    public function index(VideoConference $conference): JsonResponse
    {
        $captions = Caption::where('conference_id', $conference->id)
            ->orderBy('created_at')
            ->get();

        return response()->json($captions);
    }

    /**
     * Translate a caption to another language.
     */
    public function translate(Request $request, Caption $caption): JsonResponse
    {
        $validated = $request->validate([
            'target_language' => ['required', 'string', 'max:10'],
        ]);

        try {
            $translated = $this->captionService->translateCaption(
                $caption,
                $validated['target_language']
            );

            return response()->json($translated);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Get full transcript for a conference.
     */
    public function transcript(VideoConference $conference): JsonResponse
    {
        try {
            $transcript = $this->captionService->getTranscript($conference);

            return response()->json($transcript);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
