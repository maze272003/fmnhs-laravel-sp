<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BreakoutRoom;
use App\Models\Student;
use App\Models\VideoConference;
use App\Services\BreakoutRoomService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BreakoutRoomApiController extends Controller
{
    public function __construct(
        private readonly BreakoutRoomService $breakoutRoomService,
    ) {}

    /**
     * List breakout rooms for a conference.
     */
    public function index(VideoConference $conference): JsonResponse
    {
        $rooms = BreakoutRoom::where('conference_id', $conference->id)
            ->with('participants')
            ->orderBy('created_at')
            ->get();

        return response()->json($rooms);
    }

    /**
     * Create breakout rooms for a conference.
     */
    public function store(Request $request, VideoConference $conference): JsonResponse
    {
        $validated = $request->validate([
            'count' => ['required', 'integer', 'min:1', 'max:20'],
            'settings' => ['nullable', 'array'],
        ]);

        try {
            $rooms = $this->breakoutRoomService->createRooms(
                $conference,
                $validated['count'],
                $validated['settings'] ?? []
            );

            return response()->json($rooms, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Assign students to a breakout room.
     */
    public function assignStudents(Request $request, BreakoutRoom $room): JsonResponse
    {
        $validated = $request->validate([
            'student_ids' => ['required', 'array'],
            'student_ids.*' => ['integer', 'exists:students,id'],
        ]);

        try {
            $participants = [];
            foreach ($validated['student_ids'] as $studentId) {
                $student = Student::findOrFail($studentId);
                $participants[] = $this->breakoutRoomService->assignStudent($room, $student);
            }

            return response()->json($room->load('participants'));
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Auto-assign students to breakout rooms (delegates to assignStudents).
     */
    public function autoAssign(Request $request, BreakoutRoom $room): JsonResponse
    {
        return $this->assignStudents($request, $room);
    }

    /**
     * Join a breakout room.
     */
    public function join(BreakoutRoom $room): JsonResponse
    {
        $student = Auth::user();

        try {
            $participant = $this->breakoutRoomService->assignStudent($room, $student);

            return response()->json(['message' => 'Joined breakout room successfully.']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Leave a breakout room.
     */
    public function leave(BreakoutRoom $room): JsonResponse
    {
        $student = Auth::user();

        try {
            $this->breakoutRoomService->removeStudent($room, $student);

            return response()->json(['message' => 'Left breakout room successfully.']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Broadcast a message to all breakout rooms.
     */
    public function broadcast(Request $request, VideoConference $conference): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        try {
            $result = $this->breakoutRoomService->broadcastToAll($conference, $validated['message']);

            return response()->json(['message' => 'Broadcast sent successfully.', 'result' => $result]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Close a breakout room.
     */
    public function close(BreakoutRoom $room): JsonResponse
    {
        try {
            $this->breakoutRoomService->closeRoom($room);

            return response()->json(['message' => 'Breakout room closed.']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * End all breakout rooms for a conference (delegates to closeAll).
     */
    public function endAll(VideoConference $conference): JsonResponse
    {
        return $this->closeAll($conference);
    }

    /**
     * Close all breakout rooms for a conference.
     */
    public function closeAll(VideoConference $conference): JsonResponse
    {
        try {
            $this->breakoutRoomService->closeAllRooms($conference);

            return response()->json(['message' => 'All breakout rooms closed.']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
