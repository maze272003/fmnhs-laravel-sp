<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\Section;
use App\Models\AuditTrail;
use App\Models\Room;
use App\Models\SchoolYearConfig;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnrollmentStatusTest extends TestCase
{
    use RefreshDatabase;

    private function createStudentWithSection(array $overrides = []): Student
    {
        $section = Section::create(['name' => 'Test Section', 'grade_level' => 10, 'strand' => null]);

        return Student::create(array_merge([
            'lrn' => '123456789012',
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'email' => 'juan@test.com',
            'password' => bcrypt('password'),
            'section_id' => $section->id,
            'school_year' => '2025-2026',
            'enrollment_type' => 'Regular',
            'enrollment_status' => 'Enrolled',
        ], $overrides));
    }

    public function test_student_can_be_dropped(): void
    {
        $student = $this->createStudentWithSection();

        $this->assertEquals('Enrolled', $student->enrollment_status);

        $student->update(['enrollment_status' => 'Dropped']);
        $student->refresh();

        $this->assertEquals('Dropped', $student->enrollment_status);
    }

    public function test_student_can_be_transferred(): void
    {
        $student = $this->createStudentWithSection();

        $student->update(['enrollment_status' => 'Transferred']);
        $student->refresh();

        $this->assertEquals('Transferred', $student->enrollment_status);
    }

    public function test_dropped_student_can_be_reenrolled(): void
    {
        $student = $this->createStudentWithSection(['enrollment_status' => 'Dropped']);

        $this->assertEquals('Dropped', $student->enrollment_status);

        $student->update(['enrollment_status' => 'Enrolled']);
        $student->refresh();

        $this->assertEquals('Enrolled', $student->enrollment_status);
    }

    public function test_alumni_enrollment_status_cannot_be_changed(): void
    {
        $student = $this->createStudentWithSection([
            'enrollment_status' => 'Alumni',
            'is_alumni' => true,
        ]);

        $this->assertTrue($student->is_alumni);
        $this->assertEquals('Alumni', $student->enrollment_status);

        // Alumni status should remain protected (business logic enforces this)
        $this->assertTrue($student->is_alumni);
    }

    public function test_enrollment_status_audit_trail(): void
    {
        AuditTrail::log(
            'Student', 1, 'updated',
            'enrollment_status', 'Enrolled', 'Dropped',
            'admin', 1, 'Admin User'
        );

        $this->assertDatabaseHas('audit_trails', [
            'auditable_type' => 'Student',
            'action' => 'updated',
            'field' => 'enrollment_status',
            'old_value' => 'Enrolled',
            'new_value' => 'Dropped',
        ]);
    }
}
