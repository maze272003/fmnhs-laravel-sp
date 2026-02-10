<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VideoConference;
use App\Services\ConferenceDataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConferenceApiController extends Controller
{
    public function __construct(
        private readonly ConferenceDataService $dataService,
    ) {}

    /**
     * Save a chat message.
     */
    public function storeMessage(Request $request, VideoConference $conference): JsonResponse
    {
        $this->authorizeAccess($conference);

        $validated = $request->validate([
            'content' => ['required', 'string', 'max:2000'],
        ]);

        $actor = $this->resolveActor($conference);

        $message = $this->dataService->saveMessage(
            $conference,
            $actor['id'],
            $actor['name'],
            $actor['role'],
            $validated['content'],
        );

        return response()->json([
            'id' => $message->id,
            'actor_id' => $message->actor_id,
            'display_name' => $message->display_name,
            'content' => $message->content,
            'type' => $message->type,
            'elapsed_seconds' => $message->conference_elapsed_seconds,
            'timestamp' => $message->created_at->toIso8601String(),
        ], 201);
    }

    /**
     * Upload a file to chat.
     */
    public function uploadFile(Request $request, VideoConference $conference): JsonResponse
    {
        $this->authorizeAccess($conference);

        $request->validate([
            'file' => ['required', 'file', 'max:20480'], // 20MB max
        ]);

        $file = $request->file('file');
        $disk = config('conference.file_disk', 's3');
        $path = $file->store("conference-files/{$conference->id}", $disk);

        $actor = $this->resolveActor($conference);

        $message = $this->dataService->saveFileMessage(
            $conference,
            $actor['id'],
            $actor['name'],
            $actor['role'],
            $path,
            $file->getClientOriginalName(),
            $file->getMimeType(),
            $file->getSize(),
        );

        return response()->json([
            'id' => $message->id,
            'actor_id' => $message->actor_id,
            'display_name' => $message->display_name,
            'content' => $message->content,
            'type' => 'file',
            'file_name' => $message->file_name,
            'file_mime' => $message->file_mime,
            'file_size' => $message->file_size,
            'file_url' => $this->dataService->getFileUrl($message),
            'elapsed_seconds' => $message->conference_elapsed_seconds,
            'timestamp' => $message->created_at->toIso8601String(),
        ], 201);
    }

    /**
     * Get chat history.
     */
    public function getMessages(VideoConference $conference): JsonResponse
    {
        $this->authorizeAccess($conference);

        return response()->json([
            'messages' => $this->dataService->getChatReplay($conference),
        ]);
    }

    /**
     * Get participant list.
     */
    public function getParticipants(VideoConference $conference): JsonResponse
    {
        $this->authorizeAccess($conference);

        $participants = $conference->participants()
            ->orderBy('joined_at')
            ->get()
            ->map(fn ($p) => [
                'actor_id' => $p->actor_id,
                'display_name' => $p->display_name,
                'role' => $p->role,
                'is_online' => $p->isOnline(),
                'joined_at' => $p->joined_at?->toIso8601String(),
                'left_at' => $p->left_at?->toIso8601String(),
                'is_guest' => $p->is_guest,
            ]);

        return response()->json(['participants' => $participants]);
    }

    /**
     * Log a conference event from the client.
     */
    public function logEvent(Request $request, VideoConference $conference): JsonResponse
    {
        $this->authorizeAccess($conference);

        $validated = $request->validate([
            'event_type' => ['required', 'string', 'max:50'],
            'metadata' => ['nullable', 'array'],
        ]);

        $actor = $this->resolveActor($conference);

        $this->dataService->logEvent(
            $conference,
            $actor['id'],
            $validated['event_type'],
            $validated['metadata'] ?? null,
        );

        return response()->json(['status' => 'ok']);
    }

    /**
     * Record participant join (called from signaling or client).
     */
    public function recordJoin(Request $request, VideoConference $conference): JsonResponse
    {
        $this->authorizeAccess($conference);

        $actor = $this->resolveActor($conference);

        $participant = $this->dataService->recordJoin(
            $conference,
            $actor['id'],
            $actor['name'],
            $actor['role'],
            false,
            $request->input('device_info'),
        );

        return response()->json([
            'participant_id' => $participant->id,
            'actor_id' => $participant->actor_id,
        ]);
    }

    /**
     * Record participant leave.
     */
    public function recordLeave(VideoConference $conference): JsonResponse
    {
        $this->authorizeAccess($conference);

        $actor = $this->resolveActor($conference);
        $this->dataService->recordLeave($conference, $actor['id']);

        return response()->json(['status' => 'ok']);
    }

    /**
     * Get meeting summary (teacher only).
     */
    public function getSummary(VideoConference $conference): JsonResponse
    {
        $teacher = Auth::guard('teacher')->user();
        abort_unless($teacher && (int) $conference->teacher_id === (int) $teacher->id, 403);

        return response()->json($this->dataService->getMeetingSummary($conference));
    }

    /**
     * Get event timeline.
     */
    public function getTimeline(VideoConference $conference): JsonResponse
    {
        $this->authorizeAccess($conference);

        return response()->json([
            'events' => $this->dataService->getEventTimeline($conference),
        ]);
    }

    private function authorizeAccess(VideoConference $conference): void
    {
        $teacher = Auth::guard('teacher')->user();
        $student = Auth::guard('student')->user();

        if ($teacher && (int) $conference->teacher_id === (int) $teacher->id) {
            return;
        }

        if ($student && $conference->canStudentJoin($student)) {
            return;
        }

        abort(403);
    }

    private function resolveActor(VideoConference $conference): array
    {
        $teacher = Auth::guard('teacher')->user();
        $student = Auth::guard('student')->user();

        if ($teacher && (int) $conference->teacher_id === (int) $teacher->id) {
            return [
                'id' => 'teacher-'.$teacher->id,
                'name' => trim($teacher->first_name.' '.$teacher->last_name),
                'role' => 'teacher',
            ];
        }

        if ($student) {
            return [
                'id' => 'student-'.$student->id,
                'name' => trim($student->first_name.' '.$student->last_name),
                'role' => 'student',
            ];
        }

        abort(403);
    }
}
