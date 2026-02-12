<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Presentation;
use App\Models\Slide;
use App\Models\Student;
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

        try {
            $presentation = $this->presentationService->create($validated);

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
            'file' => ['required', 'file', 'max:51200'],
        ]);

        $conference = null;
        if ($request->has('conference_id')) {
            $conference = \App\Models\VideoConference::find($request->input('conference_id'));
        }

        try {
            $presentation = $this->presentationService->upload(
                $request->file('file'),
                $conference
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
        $slides = $this->presentationService->getSlides($presentation->id);

        return response()->json($slides);
    }

    /**
     * Track a slide view.
     */
    public function trackView(Slide $slide): JsonResponse
    {
        $student = Student::findOrFail(Auth::id());

        try {
            $this->presentationService->trackSlideView($slide, $student);

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
            'slide_number' => ['required', 'integer', 'min:1'],
        ]);

        try {
            $result = $this->presentationService->syncSlideChange(
                (string) $presentation->id,
                $validated['slide_number']
            );

            return response()->json($result);
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
            $analytics = $this->presentationService->getEngagementAnalytics(
                (string) $presentation->id
            );

            return response()->json($analytics);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
