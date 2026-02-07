<?php

namespace App\Repositories\Eloquent;

use App\Models\Grade;
use App\Models\Room;
use App\Models\Schedule;
use App\Models\Section;
use App\Models\Subject;
use App\Models\Teacher;
use App\Repositories\Contracts\ScheduleRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ScheduleRepository implements ScheduleRepositoryInterface
{
    public function getSubjects(): Collection
    {
        return Subject::all();
    }

    public function getTeachers(): Collection
    {
        return Teacher::all();
    }

    public function getSections(): Collection
    {
        return Section::all();
    }

    public function getRooms(): Collection
    {
        return Room::orderBy('name')->get();
    }

    public function paginateSchedules(int $perPage = 10): LengthAwarePaginator
    {
        return Schedule::with(['subject', 'teacher', 'section'])
            ->orderBy('day')
            ->orderBy('start_time')
            ->paginate($perPage);
    }

    public function hasTeacherConflict(int $teacherId, string $day, string $startTime, string $endTime): bool
    {
        return Schedule::hasTeacherConflict($teacherId, $day, $startTime, $endTime);
    }

    public function hasRoomConflict(string $room, string $day, string $startTime, string $endTime): bool
    {
        return Schedule::hasRoomConflict($room, $day, $startTime, $endTime);
    }

    public function hasSectionConflict(int $sectionId, string $day, string $startTime, string $endTime): bool
    {
        return Schedule::hasSectionConflict($sectionId, $day, $startTime, $endTime);
    }

    public function create(array $data): Schedule
    {
        return Schedule::create($data);
    }

    public function findOrFail(int $id): Schedule
    {
        return Schedule::findOrFail($id);
    }

    public function deleteRelatedGrades(Schedule $schedule): void
    {
        Grade::where('teacher_id', $schedule->teacher_id)
            ->where('subject_id', $schedule->subject_id)
            ->whereHas('student', function ($query) use ($schedule) {
                $query->where('section_id', $schedule->section_id);
            })
            ->delete();
    }

    public function deleteSchedule(Schedule $schedule): void
    {
        $schedule->delete();
    }

    public function setRoomAvailabilityByName(string $roomName, bool $isAvailable): void
    {
        Room::where('name', $roomName)->update(['is_available' => $isAvailable]);
    }

    public function countSchedulesByRoom(string $roomName): int
    {
        return Schedule::where('room', $roomName)->count();
    }
}

