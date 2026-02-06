<?php

namespace Tests\Feature;

use App\Models\Grade;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Section;
use App\Models\Subject;
use App\Models\AuditTrail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GradeLockingTest extends TestCase
{
    use RefreshDatabase;

    public function test_grade_can_be_locked(): void
    {
        $section = Section::create(['name' => 'Test', 'grade_level' => 12, 'strand' => 'STEM']);
        $teacher = Teacher::create([
            'employee_id' => 'T001', 'first_name' => 'John', 'last_name' => 'Doe',
            'email' => 'john@test.com', 'password' => bcrypt('password'), 'department' => 'Science',
        ]);
        $subject = Subject::create(['code' => 'SCI101', 'name' => 'General Science']);
        $student = Student::create([
            'lrn' => '123456789012', 'first_name' => 'Test', 'last_name' => 'Student',
            'email' => 'test@student.com', 'password' => bcrypt('password'),
            'section_id' => $section->id, 'school_year' => '2025-2026', 'enrollment_type' => 'Regular',
        ]);

        $grade = Grade::create([
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
            'quarter' => 1,
            'grade_value' => 90.5,
            'school_year' => '2025-2026',
        ]);

        $this->assertFalse((bool)$grade->is_locked);

        // Lock the grade
        $grade->update([
            'is_locked' => true,
            'locked_at' => now(),
            'locked_by' => 'Admin',
        ]);

        $grade->refresh();
        $this->assertTrue($grade->is_locked);
        $this->assertNotNull($grade->locked_at);
        $this->assertEquals('Admin', $grade->locked_by);
    }

    public function test_locked_and_unlocked_scopes(): void
    {
        $section = Section::create(['name' => 'Test', 'grade_level' => 12, 'strand' => 'STEM']);
        $teacher = Teacher::create([
            'employee_id' => 'T001', 'first_name' => 'John', 'last_name' => 'Doe',
            'email' => 'john@test.com', 'password' => bcrypt('password'), 'department' => 'Science',
        ]);
        $subject = Subject::create(['code' => 'SCI101', 'name' => 'General Science']);
        $student = Student::create([
            'lrn' => '123456789012', 'first_name' => 'Test', 'last_name' => 'Student',
            'email' => 'test@student.com', 'password' => bcrypt('password'),
            'section_id' => $section->id, 'school_year' => '2025-2026', 'enrollment_type' => 'Regular',
        ]);

        // Create locked grade
        Grade::create([
            'student_id' => $student->id, 'teacher_id' => $teacher->id,
            'subject_id' => $subject->id, 'quarter' => 1, 'grade_value' => 90,
            'school_year' => '2025-2026', 'is_locked' => true,
        ]);

        // Create unlocked grade
        Grade::create([
            'student_id' => $student->id, 'teacher_id' => $teacher->id,
            'subject_id' => $subject->id, 'quarter' => 2, 'grade_value' => 85,
            'school_year' => '2025-2026', 'is_locked' => false,
        ]);

        $this->assertEquals(1, Grade::locked()->count());
        $this->assertEquals(1, Grade::unlocked()->count());
    }

    public function test_audit_trail_logging(): void
    {
        AuditTrail::log(
            'Grade', 1, 'updated',
            'grade_value', '85.00', '90.00',
            'teacher', 1, 'John Doe'
        );

        $this->assertDatabaseHas('audit_trails', [
            'auditable_type' => 'Grade',
            'auditable_id' => 1,
            'action' => 'updated',
            'field' => 'grade_value',
            'old_value' => '85.00',
            'new_value' => '90.00',
            'user_type' => 'teacher',
            'user_name' => 'John Doe',
        ]);
    }
}
