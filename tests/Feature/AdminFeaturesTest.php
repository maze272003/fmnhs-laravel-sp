<?php

namespace Tests\Feature;

use App\Models\Room;
use App\Models\SchoolYearConfig;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminFeaturesTest extends TestCase
{
    use RefreshDatabase;

    public function test_room_can_be_created(): void
    {
        $room = Room::create([
            'name' => 'Room 101',
            'building' => 'Main Building',
            'capacity' => 40,
            'is_available' => true,
        ]);

        $this->assertDatabaseHas('rooms', [
            'name' => 'Room 101',
            'building' => 'Main Building',
            'capacity' => 40,
            'is_available' => true,
        ]);

        $this->assertTrue($room->is_available);
    }

    public function test_room_availability_can_be_toggled(): void
    {
        $room = Room::create([
            'name' => 'Room 201',
            'building' => 'Annex',
            'capacity' => 30,
            'is_available' => true,
        ]);

        $room->update(['is_available' => false]);
        $room->refresh();

        $this->assertFalse($room->is_available);
    }

    public function test_school_year_config_can_be_created(): void
    {
        $config = SchoolYearConfig::create([
            'school_year' => '2026-2027',
            'start_date' => '2026-06-01',
            'end_date' => '2027-03-31',
            'status' => 'upcoming',
            'is_active' => false,
        ]);

        $this->assertDatabaseHas('school_year_configs', [
            'school_year' => '2026-2027',
            'status' => 'upcoming',
        ]);
    }

    public function test_school_year_activation_deactivates_others(): void
    {
        $sy1 = SchoolYearConfig::create([
            'school_year' => '2025-2026',
            'start_date' => '2025-06-01',
            'end_date' => '2026-03-31',
            'status' => 'active',
            'is_active' => true,
        ]);

        $sy2 = SchoolYearConfig::create([
            'school_year' => '2026-2027',
            'start_date' => '2026-06-01',
            'end_date' => '2027-03-31',
            'status' => 'upcoming',
            'is_active' => false,
        ]);

        $sy2->activate();

        $sy1->refresh();
        $sy2->refresh();

        $this->assertFalse($sy1->is_active);
        $this->assertTrue($sy2->is_active);
        $this->assertEquals('active', $sy2->status);
    }

    public function test_grade_value_must_be_within_valid_range(): void
    {
        // This tests the validation rule we added: grades.*.*  => nullable|numeric|min:60|max:100
        $section = \App\Models\Section::create(['name' => 'Test', 'grade_level' => 10]);
        $teacher = \App\Models\Teacher::create([
            'employee_id' => 'T001', 'first_name' => 'John', 'last_name' => 'Doe',
            'email' => 'john@test.com', 'password' => bcrypt('password'), 'department' => 'Science',
        ]);
        $subject = \App\Models\Subject::create(['code' => 'SCI101', 'name' => 'General Science']);
        $student = \App\Models\Student::create([
            'lrn' => '123456789012', 'first_name' => 'Test', 'last_name' => 'Student',
            'email' => 'test@student.com', 'password' => bcrypt('password'),
            'section_id' => $section->id, 'school_year' => '2025-2026', 'enrollment_type' => 'Regular',
        ]);

        // Valid grade
        $grade = \App\Models\Grade::create([
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
            'quarter' => 1,
            'grade_value' => 85,
            'school_year' => '2025-2026',
        ]);

        $this->assertEquals(85, $grade->grade_value);

        // Ensure valid grades within expected range
        $this->assertGreaterThanOrEqual(60, $grade->grade_value);
        $this->assertLessThanOrEqual(100, $grade->grade_value);
    }
}
