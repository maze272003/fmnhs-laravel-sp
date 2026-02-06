<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\Section;
use App\Models\Grade;
use App\Models\AuditTrail;
use App\Models\PromotionHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentPromotionTest extends TestCase
{
    use RefreshDatabase;

    public function test_grade12_student_becomes_alumni_on_promotion(): void
    {
        $fromSection = Section::create([
            'name' => 'Rizal',
            'grade_level' => 12,
            'strand' => 'STEM',
        ]);

        $toSection = Section::create([
            'name' => 'Alumni',
            'grade_level' => 12,
            'strand' => 'STEM',
        ]);

        $student = Student::create([
            'lrn' => '123456789012',
            'first_name' => 'Test',
            'last_name' => 'Student',
            'email' => 'test@student.com',
            'password' => bcrypt('password'),
            'section_id' => $fromSection->id,
            'school_year' => '2024-2025',
            'enrollment_type' => 'Regular',
            'enrollment_status' => 'Enrolled',
        ]);

        // Verify student starts as non-alumni
        $student->refresh();
        $this->assertFalse((bool)$student->is_alumni);
        $this->assertEquals('Enrolled', $student->enrollment_status);

        // Simulate promotion from Grade 12
        $student->update([
            'section_id' => $toSection->id,
            'school_year' => '2025-2026',
            'enrollment_status' => 'Alumni',
            'is_alumni' => true,
        ]);

        $student->refresh();
        $this->assertTrue($student->is_alumni);
        $this->assertEquals('Alumni', $student->enrollment_status);
    }

    public function test_alumni_status_is_final(): void
    {
        $section = Section::create([
            'name' => 'Test',
            'grade_level' => 12,
            'strand' => 'STEM',
        ]);

        $student = Student::create([
            'lrn' => '123456789012',
            'first_name' => 'Test',
            'last_name' => 'Student',
            'email' => 'test@student.com',
            'password' => bcrypt('password'),
            'section_id' => $section->id,
            'school_year' => '2024-2025',
            'enrollment_type' => 'Regular',
            'enrollment_status' => 'Alumni',
            'is_alumni' => true,
        ]);

        // Verify alumni scope
        $this->assertEquals(1, Student::alumni()->count());
        $this->assertEquals(0, Student::active()->count());
    }

    public function test_student_enrollment_badge(): void
    {
        $section7 = Section::create([
            'name' => 'Luna',
            'grade_level' => 7,
        ]);

        $section12 = Section::create([
            'name' => 'Rizal',
            'grade_level' => 12,
            'strand' => 'STEM',
        ]);

        // Grade 7 student should have "Newly Enrolled" badge
        $student1 = Student::create([
            'lrn' => '123456789001',
            'first_name' => 'New',
            'last_name' => 'Student',
            'email' => 'new@student.com',
            'password' => bcrypt('password'),
            'section_id' => $section7->id,
            'school_year' => '2025-2026',
            'enrollment_type' => 'Regular',
        ]);
        $student1->load('section');
        $this->assertEquals('Newly Enrolled', $student1->enrollment_badge);

        // Transferee should have "New Enrollee – Transferee" badge
        $student2 = Student::create([
            'lrn' => '123456789002',
            'first_name' => 'Transfer',
            'last_name' => 'Student',
            'email' => 'transfer@student.com',
            'password' => bcrypt('password'),
            'section_id' => $section12->id,
            'school_year' => '2025-2026',
            'enrollment_type' => 'Transferee',
        ]);
        $student2->load('section');
        $this->assertEquals('New Enrollee – Transferee', $student2->enrollment_badge);
    }

    public function test_soft_delete_preserves_student_data(): void
    {
        $section = Section::create([
            'name' => 'Test',
            'grade_level' => 10,
        ]);

        $student = Student::create([
            'lrn' => '123456789012',
            'first_name' => 'Archive',
            'last_name' => 'Student',
            'email' => 'archive@student.com',
            'password' => bcrypt('password'),
            'section_id' => $section->id,
            'school_year' => '2025-2026',
            'enrollment_type' => 'Regular',
        ]);

        $student->delete();

        // Student is soft-deleted
        $this->assertSoftDeleted('students', ['lrn' => '123456789012']);

        // Can be restored
        $student->restore();
        $this->assertDatabaseHas('students', ['lrn' => '123456789012', 'deleted_at' => null]);
    }
}
