<?php

namespace App\Repositories\Contracts;

use App\Models\Schedule;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface ScheduleRepositoryInterface
{
    public function getSubjects(): Collection;

    public function getTeachers(): Collection;

    public function getSections(): Collection;

    public function getRooms(): Collection;

    public function paginateSchedules(int $perPage = 10): LengthAwarePaginator;

    public function hasTeacherConflict(int $teacherId, string $day, string $startTime, string $endTime): bool;

    public function hasRoomConflict(string $room, string $day, string $startTime, string $endTime): bool;

    public function hasSectionConflict(int $sectionId, string $day, string $startTime, string $endTime): bool;

    public function create(array $data): Schedule;

    public function findOrFail(int $id): Schedule;

    public function deleteRelatedGrades(Schedule $schedule): void;

    public function deleteSchedule(Schedule $schedule): void;

    public function setRoomAvailabilityByName(string $roomName, bool $isAvailable): void;

    public function countSchedulesByRoom(string $roomName): int;
}

