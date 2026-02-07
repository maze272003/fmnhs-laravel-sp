<?php

namespace App\Services;

use App\Models\AuditTrail;
use App\Models\Schedule;
use App\Repositories\Contracts\ScheduleRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ScheduleManagementService
{
    public function __construct(private readonly ScheduleRepositoryInterface $schedules)
    {
    }

    public function create(array $validated, ?object $adminUser = null): Schedule
    {
        $this->validateConflicts($validated);

        $schedule = DB::transaction(function () use ($validated) {
            $schedule = $this->schedules->create($validated);
            $this->schedules->setRoomAvailabilityByName($validated['room'], false);
            return $schedule;
        });

        AuditTrail::log(
            'Schedule',
            $schedule->id,
            'created',
            null,
            null,
            $schedule->toArray(),
            'admin',
            $adminUser?->id,
            $adminUser?->name ?? 'Admin'
        );

        return $schedule;
    }

    public function delete(int $scheduleId, ?object $adminUser = null): void
    {
        $schedule = $this->schedules->findOrFail($scheduleId);
        $scheduleData = $schedule->toArray();
        $roomName = $schedule->room;

        DB::transaction(function () use ($schedule, $roomName) {
            $this->schedules->deleteRelatedGrades($schedule);
            $this->schedules->deleteSchedule($schedule);

            if ($this->schedules->countSchedulesByRoom($roomName) === 0) {
                $this->schedules->setRoomAvailabilityByName($roomName, true);
            }
        });

        AuditTrail::log(
            'Schedule',
            $scheduleId,
            'deleted',
            null,
            $scheduleData,
            null,
            'admin',
            $adminUser?->id,
            $adminUser?->name ?? 'Admin'
        );
    }

    private function validateConflicts(array $validated): void
    {
        if ($this->schedules->hasTeacherConflict(
            $validated['teacher_id'],
            $validated['day'],
            $validated['start_time'],
            $validated['end_time']
        )) {
            throw ValidationException::withMessages([
                'teacher_id' => 'Time conflict: Teacher already has a class.',
            ]);
        }

        if ($this->schedules->hasRoomConflict(
            $validated['room'],
            $validated['day'],
            $validated['start_time'],
            $validated['end_time']
        )) {
            throw ValidationException::withMessages([
                'room' => 'Room conflict: Room already occupied.',
            ]);
        }

        if ($this->schedules->hasSectionConflict(
            $validated['section_id'],
            $validated['day'],
            $validated['start_time'],
            $validated['end_time']
        )) {
            throw ValidationException::withMessages([
                'section_id' => 'Time conflict: Section already has a class.',
            ]);
        }
    }
}

