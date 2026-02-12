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
            'language' => ['sometimes', 'string', 'max:10'],
            'speaker_type' => ['nullable', 'string'],
            'speaker_id' => ['nullable', 'integer'],
        ]);

        try {
            $speaker = null;
            if (! empty($validated['speaker_type']) && ! empty($validated['speaker_id'])) {
                $speaker = [
                    'type' => $validated['speaker_type'],
                    'id' => $validated['speaker_id'],
                ];
            }

            $caption = $this->captionService->addCaption(
                $conference,
                $validated['text'],
                $validated['language'] ?? 'en',
                $speaker
            );

            return response()->json($caption, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * List captions for a conference.
     */
    public function index(Request $request, VideoConference $conference): JsonResponse
    {
        $language = $request->query('language', 'en');
        $captions = $this->captionService->getCaptions($conference, $language);

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
