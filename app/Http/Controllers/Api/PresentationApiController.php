<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Presentation;
use App\Models\Slide;
use App\Services\PresentationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PresentationApiController extends Controller
{
    public function __construct(
        private readonly PresentationService $presentationService,
    ) {}

    /**
     * Create a new presentation.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'conference_id' => ['nullable', 'exists:video_conferences,id'],
        ]);

        $validated['teacher_id'] = Auth::id();

        try {
            $presentation = $this->presentationService->createPresentation($validated);

            return response()->json($presentation, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Upload a presentation file.
     */
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'max:51200'], // 50MB max
            'title' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $presentation = $this->presentationService->uploadPresentation(
                $request->file('file'),
                Auth::id(),
                $request->input('title')
            );

            return response()->json($presentation, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Get a presentation.
     */
    public function show(Presentation $presentation): JsonResponse
    {
        $presentation->load('slides');

        return response()->json($presentation);
    }

    /**
     * Get slides for a presentation.
     */
    public function slides(Presentation $presentation): JsonResponse
    {
        $slides = $presentation->slides()->orderBy('order')->get();

        return response()->json($slides);
    }

    /**
     * Track a slide view.
     */
    public function trackView(Request $request, Slide $slide): JsonResponse
    {
        $validated = $request->validate([
            'duration' => ['nullable', 'integer', 'min:0'],
        ]);

        try {
            $this->presentationService->trackSlideView(
                $slide,
                Auth::id(),
                $validated['duration'] ?? null
            );

            return response()->json(['message' => 'View tracked.']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Sync the current slide for all viewers.
     */
    public function syncSlide(Request $request, Presentation $presentation): JsonResponse
    {
        $validated = $request->validate([
            'slide_index' => ['required', 'integer', 'min:0'],
        ]);

        try {
            $this->presentationService->syncSlide($presentation, $validated['slide_index']);

            return response()->json(['message' => 'Slide synced.']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Get presentation analytics.
     */
    public function analytics(Presentation $presentation): JsonResponse
    {
        try {
            $analytics = $this->presentationService->getAnalytics($presentation);

            return response()->json($analytics);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
