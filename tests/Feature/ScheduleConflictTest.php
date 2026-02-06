<?php

namespace Tests\Feature;

use App\Models\Schedule;
use App\Models\Teacher;
use App\Models\Section;
use App\Models\Subject;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduleConflictTest extends TestCase
{
    use RefreshDatabase;

    private function createSchedulePrerequisites(): array
    {
        $teacher = Teacher::create([
            'employee_id' => 'T001',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@test.com',
            'password' => bcrypt('password'),
            'department' => 'Science',
        ]);

        $teacher2 = Teacher::create([
            'employee_id' => 'T002',
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane@test.com',
            'password' => bcrypt('password'),
            'department' => 'Math',
        ]);

        $section = Section::create([
            'name' => 'Rizal',
            'grade_level' => 12,
            'strand' => 'STEM',
        ]);

        $section2 = Section::create([
            'name' => 'Bonifacio',
            'grade_level' => 11,
            'strand' => 'ABM',
        ]);

        $subject = Subject::create([
            'code' => 'SCI101',
            'name' => 'General Science',
        ]);

        $subject2 = Subject::create([
            'code' => 'MATH101',
            'name' => 'General Math',
        ]);

        return compact('teacher', 'teacher2', 'section', 'section2', 'subject', 'subject2');
    }

    public function test_teacher_schedule_conflict_is_detected(): void
    {
        $data = $this->createSchedulePrerequisites();

        // Create an existing schedule
        Schedule::create([
            'section_id' => $data['section']->id,
            'subject_id' => $data['subject']->id,
            'teacher_id' => $data['teacher']->id,
            'day' => 'Monday',
            'start_time' => '08:00',
            'end_time' => '09:00',
            'room' => 'Room 101',
        ]);

        // Verify conflict detection
        $this->assertTrue(
            Schedule::hasTeacherConflict($data['teacher']->id, 'Monday', '08:30', '09:30')
        );

        // No conflict for different time
        $this->assertFalse(
            Schedule::hasTeacherConflict($data['teacher']->id, 'Monday', '09:00', '10:00')
        );

        // No conflict for different day
        $this->assertFalse(
            Schedule::hasTeacherConflict($data['teacher']->id, 'Tuesday', '08:00', '09:00')
        );
    }

    public function test_room_schedule_conflict_is_detected(): void
    {
        $data = $this->createSchedulePrerequisites();

        Schedule::create([
            'section_id' => $data['section']->id,
            'subject_id' => $data['subject']->id,
            'teacher_id' => $data['teacher']->id,
            'day' => 'Monday',
            'start_time' => '08:00',
            'end_time' => '09:00',
            'room' => 'Room 101',
        ]);

        // Same room, overlapping time = conflict
        $this->assertTrue(
            Schedule::hasRoomConflict('Room 101', 'Monday', '08:30', '09:30')
        );

        // Different room = no conflict
        $this->assertFalse(
            Schedule::hasRoomConflict('Room 102', 'Monday', '08:30', '09:30')
        );
    }

    public function test_section_schedule_conflict_is_detected(): void
    {
        $data = $this->createSchedulePrerequisites();

        Schedule::create([
            'section_id' => $data['section']->id,
            'subject_id' => $data['subject']->id,
            'teacher_id' => $data['teacher']->id,
            'day' => 'Monday',
            'start_time' => '08:00',
            'end_time' => '09:00',
            'room' => 'Room 101',
        ]);

        // Same section, overlapping time = conflict
        $this->assertTrue(
            Schedule::hasSectionConflict($data['section']->id, 'Monday', '08:30', '09:30')
        );

        // Different section = no conflict
        $this->assertFalse(
            Schedule::hasSectionConflict($data['section2']->id, 'Monday', '08:00', '09:00')
        );
    }

    public function test_room_availability_check(): void
    {
        $data = $this->createSchedulePrerequisites();

        $room = Room::create([
            'name' => 'Test Room',
            'building' => 'Main',
            'capacity' => 40,
            'is_available' => true,
        ]);

        Schedule::create([
            'section_id' => $data['section']->id,
            'subject_id' => $data['subject']->id,
            'teacher_id' => $data['teacher']->id,
            'day' => 'Monday',
            'start_time' => '08:00',
            'end_time' => '09:00',
            'room' => $room->name,
            'room_id' => $room->id,
        ]);

        // Room is NOT available at overlapping time
        $this->assertFalse(
            $room->isAvailableAt('Monday', '08:30', '09:30')
        );

        // Room IS available at non-overlapping time
        $this->assertTrue(
            $room->isAvailableAt('Monday', '09:00', '10:00')
        );
    }
}
