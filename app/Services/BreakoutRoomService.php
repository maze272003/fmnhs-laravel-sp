<?php

namespace App\Services;

use App\Models\BreakoutRoom;
use App\Models\BreakoutRoomParticipant;
use App\Models\Student;
use App\Models\VideoConference;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BreakoutRoomService
{
    /**
     * Create multiple breakout rooms for a conference.
     */
    public function createRooms(VideoConference $conference, int $count, array $settings = []): Collection
    {
        $rooms = collect();

        for ($i = 1; $i <= $count; $i++) {
            $rooms->push(BreakoutRoom::create([
                'conference_id' => $conference->id,
                'name' => "Room {$i}",
                'settings' => $settings,
                'duration_minutes' => $settings['duration_minutes'] ?? 15,
                'is_active' => true,
            ]));
        }

        return $rooms;
    }

    /**
     * Auto-assign students to breakout rooms.
     */
    public function autoAssignStudents(VideoConference $conference, string $method = 'random'): void
    {
        $rooms = $this->getActiveRooms($conference);
        if ($rooms->isEmpty()) {
            return;
        }

        $students = $conference->section?->students()->get() ?? collect();
        if ($students->isEmpty()) {
            return;
        }

        $studentList = $method === 'random' ? $students->shuffle() : $students;

        $roomCount = $rooms->count();
        $studentList->each(function (Student $student, int $index) use ($rooms, $roomCount) {
            $room = $rooms[$index % $roomCount];
            $this->assignStudent($room, $student);
        });
    }

    /**
     * Assign a student to a breakout room.
     */
    public function assignStudent(BreakoutRoom $room, Student $student): BreakoutRoomParticipant
    {
        return BreakoutRoomParticipant::updateOrCreate(
            [
                'breakout_room_id' => $room->id,
                'student_id' => $student->id,
            ],
            [
                'joined_at' => now(),
                'left_at' => null,
            ]
        );
    }

    /**
     * Remove a student from a breakout room.
     */
    public function removeStudent(BreakoutRoom $room, Student $student): void
    {
        BreakoutRoomParticipant::where('breakout_room_id', $room->id)
            ->where('student_id', $student->id)
            ->update(['left_at' => now()]);
    }

    /**
     * Get all active breakout rooms for a conference.
     */
    public function getActiveRooms(VideoConference $conference): Collection
    {
        return BreakoutRoom::where('conference_id', $conference->id)
            ->where('is_active', true)
            ->with('participants')
            ->get();
    }

    /**
     * Close a single breakout room.
     */
    public function closeRoom(BreakoutRoom $room): void
    {
        $room->participants()
            ->whereNull('left_at')
            ->update(['left_at' => now()]);

        $room->update(['is_active' => false]);
    }

    /**
     * Close all breakout rooms for a conference.
     */
    public function closeAllRooms(VideoConference $conference): void
    {
        $rooms = $this->getActiveRooms($conference);

        foreach ($rooms as $room) {
            $this->closeRoom($room);
        }
    }

    /**
     * Broadcast a message to all breakout rooms in a conference.
     */
    public function broadcastToAll(VideoConference $conference, string $message): array
    {
        $rooms = $this->getActiveRooms($conference);
        $notified = [];

        foreach ($rooms as $room) {
            $participantIds = $room->participants()
                ->whereNull('left_at')
                ->pluck('student_id')
                ->toArray();

            $notified[$room->name] = $participantIds;
        }

        return [
            'message' => $message,
            'rooms_notified' => $rooms->count(),
            'participants_per_room' => $notified,
        ];
    }
}
