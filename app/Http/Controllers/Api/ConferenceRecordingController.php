<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ConferenceRecording;
use App\Models\VideoConference;
use App\Services\ConferenceRecordingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConferenceRecordingController extends Controller
{
    public function __construct(
        private readonly ConferenceRecordingService $recordingService,
    ) {}

    /**
     * Upload a recording.
     */
    public function store(Request $request, VideoConference $conference): JsonResponse
    {
        $this->ensureTeacher($conference);

        $validated = $request->validate([
            'recording' => ['required', 'file', 'max:512000'], // 500MB
            'type' => ['sometimes', 'string', 'in:video,audio'],
            'title' => ['sometimes', 'string', 'max:255'],
        ]);

        $recording = $this->recordingService->storeRecording(
            $conference,
            $request->file('recording'),
            $validated['type'] ?? 'video',
            $validated['title'] ?? null,
        );

        // Auto-generate chapters
        $this->recordingService->autoGenerateChapters($recording);

        return response()->json([
            'id' => $recording->id,
            'title' => $recording->title,
            'type' => $recording->type,
            'status' => $recording->status,
            'duration_seconds' => $recording->duration_seconds,
            'file_size' => $recording->file_size,
            'chapters' => $recording->chapters,
            'download_url' => $recording->getDownloadUrl(),
        ], 201);
    }

    /**
     * List recordings for a conference.
     */
    public function index(VideoConference $conference): JsonResponse
    {
        $this->authorizeAccess($conference);

        $recordings = $conference->recordings()
            ->where('status', 'ready')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (ConferenceRecording $r) => [
                'id' => $r->id,
                'title' => $r->title,
                'type' => $r->type,
                'duration_seconds' => $r->duration_seconds,
                'file_size' => $r->file_size,
                'mime_type' => $r->mime_type,
                'chapters' => $r->chapters ?? [],
                'has_transcript' => $r->transcript_path !== null,
                'download_url' => $r->getDownloadUrl(),
                'created_at' => $r->created_at->toIso8601String(),
            ]);

        return response()->json(['recordings' => $recordings]);
    }

    /**
     * Get a recording with chat replay data for synchronized playback.
     */
    public function show(VideoConference $conference, ConferenceRecording $recording): JsonResponse
    {
        $this->authorizeAccess($conference);
        abort_unless((int) $recording->conference_id === (int) $conference->id, 404);

        return response()->json(
            $this->recordingService->getRecordingWithChatReplay($recording)
        );
    }

    /**
     * Download transcript.
     */
    public function transcript(VideoConference $conference, ConferenceRecording $recording): JsonResponse
    {
        $this->authorizeAccess($conference);
        abort_unless((int) $recording->conference_id === (int) $conference->id, 404);

        $content = $this->recordingService->getTranscriptContent($recording);
        abort_unless($content !== null, 404, 'No transcript available.');

        return response()->json(['transcript' => $content]);
    }

    /**
     * Update chapter markers (teacher only).
     */
    public function updateChapters(Request $request, VideoConference $conference, ConferenceRecording $recording): JsonResponse
    {
        $this->ensureTeacher($conference);
        abort_unless((int) $recording->conference_id === (int) $conference->id, 404);

        $validated = $request->validate([
            'chapters' => ['required', 'array'],
            'chapters.*.time' => ['required', 'integer', 'min:0'],
            'chapters.*.title' => ['required', 'string', 'max:255'],
        ]);

        $this->recordingService->addChapters($recording, $validated['chapters']);

        return response()->json(['chapters' => $recording->fresh()->chapters]);
    }

    /**
     * Delete a recording (teacher only).
     */
    public function destroy(VideoConference $conference, ConferenceRecording $recording): JsonResponse
    {
        $this->ensureTeacher($conference);
        abort_unless((int) $recording->conference_id === (int) $conference->id, 404);

        $this->recordingService->deleteRecording($recording);

        return response()->json(['status' => 'deleted']);
    }

    private function ensureTeacher(VideoConference $conference): void
    {
        $teacher = Auth::guard('teacher')->user();
        abort_unless($teacher && (int) $conference->teacher_id === (int) $teacher->id, 403);
    }

    private function authorizeAccess(VideoConference $conference): void
    {
        $teacher = Auth::guard('teacher')->user();
        $student = Auth::guard('student')->user();

        if ($teacher && (int) $conference->teacher_id === (int) $teacher->id) {
            return;
        }

        if ($student && $conference->canStudentJoin($student)) {
            $recording = $conference->recordings()->where('restricted', true)->exists();
            // Students can still see non-restricted recordings
            return;
        }

        abort(403);
    }
}
