<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BreakoutRoom;
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
     * Create a new breakout room.
     */
    public function store(Request $request, VideoConference $conference): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'max_participants' => ['nullable', 'integer', 'min:1'],
        ]);

        try {
            $room = $this->breakoutRoomService->createRoom($conference, $validated);

            return response()->json($room, 201);
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
            $room = $this->breakoutRoomService->assignStudents($room, $validated['student_ids']);

            return response()->json($room->load('participants'));
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Join a breakout room.
     */
    public function join(BreakoutRoom $room): JsonResponse
    {
        $user = Auth::user();

        try {
            $this->breakoutRoomService->joinRoom($room, $user);

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
        $user = Auth::user();

        try {
            $this->breakoutRoomService->leaveRoom($room, $user);

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
            $this->breakoutRoomService->broadcast($conference, $validated['message']);

            return response()->json(['message' => 'Broadcast sent successfully.']);
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
