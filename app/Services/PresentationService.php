<?php

namespace App\Services;

use App\Models\Slide;
use App\Models\SlideView;
use App\Models\Student;
use App\Models\VideoConference;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PresentationService
{
    /**
     * Create a presentation with slides.
     */
    public function create(array $data): array
    {
        $presentationId = $data['presentation_id'] ?? uniqid('pres_');
        $slides = [];

        foreach (($data['slides'] ?? []) as $index => $slideData) {
            $slides[] = Slide::create([
                'presentation_id' => $presentationId,
                'order' => $index + 1,
                'content' => $slideData['content'] ?? '',
                'image_path' => $slideData['image_path'] ?? null,
                'notes' => $slideData['notes'] ?? null,
            ]);
        }

        return [
            'presentation_id' => $presentationId,
            'slides' => $slides,
            'slide_count' => count($slides),
        ];
    }

    /**
     * Upload a presentation file and extract slides.
     */
    public function upload(UploadedFile $file, ?VideoConference $conference = null): array
    {
        $path = $file->store('presentations', 'local');
        $presentationId = $conference?->id ?? uniqid('pres_');

        $slide = Slide::create([
            'presentation_id' => $presentationId,
            'order' => 1,
            'content' => $file->getClientOriginalName(),
            'image_path' => $path,
            'notes' => null,
        ]);

        return [
            'presentation_id' => $presentationId,
            'file_path' => $path,
            'slides' => [$slide],
        ];
    }

    /**
     * Get all slides for a presentation.
     */
    public function getSlides(string $presentationId): Collection
    {
        return Slide::where('presentation_id', $presentationId)
            ->orderBy('order')
            ->get();
    }

    /**
     * Track when a student views a slide.
     */
    public function trackSlideView(Slide $slide, Student $student): SlideView
    {
        return SlideView::create([
            'slide_id' => $slide->id,
            'student_id' => $student->id,
            'viewed_at' => now(),
            'duration_seconds' => 0,
        ]);
    }

    /**
     * Get engagement analytics for a presentation.
     */
    public function getEngagementAnalytics(string $presentationId): array
    {
        $slides = $this->getSlides($presentationId);
        $slideIds = $slides->pluck('id');

        $views = SlideView::whereIn('slide_id', $slideIds)->get();

        $slideEngagement = $slides->map(function (Slide $slide) use ($views) {
            $slideViews = $views->where('slide_id', $slide->id);

            return [
                'slide_id' => $slide->id,
                'order' => $slide->order,
                'view_count' => $slideViews->count(),
                'unique_viewers' => $slideViews->unique('student_id')->count(),
                'avg_duration' => round($slideViews->avg('duration_seconds') ?? 0, 1),
            ];
        });

        return [
            'presentation_id' => $presentationId,
            'total_slides' => $slides->count(),
            'total_views' => $views->count(),
            'unique_viewers' => $views->unique('student_id')->count(),
            'slide_engagement' => $slideEngagement->toArray(),
        ];
    }

    /**
     * Sync slide change to notify participants of the current slide.
     */
    public function syncSlideChange(string $presentationId, int $slideNumber): array
    {
        $slide = Slide::where('presentation_id', $presentationId)
            ->where('order', $slideNumber)
            ->first();

        return [
            'presentation_id' => $presentationId,
            'current_slide' => $slideNumber,
            'slide_id' => $slide?->id,
            'content' => $slide?->content,
            'synced_at' => now()->toIso8601String(),
        ];
    }
}
