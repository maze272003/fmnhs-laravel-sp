<?php

namespace Tests\Feature;

use App\Models\Section;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\VideoConference;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class VideoConferenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_can_create_video_conference_link(): void
    {
        $teacher = $this->createTeacher('teacher1@example.com');
        $section = Section::create([
            'name' => 'Rizal',
            'grade_level' => 10,
            'teacher_id' => $teacher->id,
        ]);

        $response = $this->actingAs($teacher, 'teacher')->post(route('teacher.conferences.store'), [
            'title' => 'Grade 10 Consultation',
            'section_id' => $section->id,
        ]);

        $response->assertRedirect(route('teacher.conferences.index'));

        $this->assertDatabaseHas('video_conferences', [
            'teacher_id' => $teacher->id,
            'section_id' => $section->id,
            'title' => 'Grade 10 Consultation',
            'is_active' => true,
        ]);
    }

    public function test_student_can_join_with_credentials_if_assigned_to_conference_section(): void
    {
        $teacher = $this->createTeacher('teacher2@example.com');
        $section = Section::create([
            'name' => 'Bonifacio',
            'grade_level' => 9,
            'teacher_id' => $teacher->id,
        ]);

        $conference = VideoConference::create([
            'teacher_id' => $teacher->id,
            'section_id' => $section->id,
            'title' => 'Science Live Class',
            'slug' => 'science-live-class',
            'is_active' => true,
        ]);

        $student = Student::create([
            'lrn' => '123456789012',
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'email' => 'juan@student.com',
            'password' => Hash::make('student-pass'),
            'section_id' => $section->id,
        ]);

        $response = $this->post(route('conference.join.attempt', $conference), [
            'credential' => 'juan@student.com',
            'password' => 'student-pass',
        ]);

        $response->assertRedirect(route('conference.room', $conference));
        $this->assertAuthenticatedAs($student, 'student');
    }

    public function test_student_is_rejected_if_not_assigned_to_teacher_meeting(): void
    {
        $teacher = $this->createTeacher('teacher3@example.com');

        $allowedSection = Section::create([
            'name' => 'Diamond',
            'grade_level' => 8,
            'teacher_id' => $teacher->id,
        ]);

        $blockedSection = Section::create([
            'name' => 'Emerald',
            'grade_level' => 8,
        ]);

        $conference = VideoConference::create([
            'teacher_id' => $teacher->id,
            'section_id' => $allowedSection->id,
            'title' => 'Restricted Room',
            'slug' => 'restricted-room',
            'is_active' => true,
        ]);

        Student::create([
            'lrn' => '999999999999',
            'first_name' => 'Ana',
            'last_name' => 'Santos',
            'email' => 'ana@student.com',
            'password' => Hash::make('student-pass'),
            'section_id' => $blockedSection->id,
        ]);

        $response = $this->from(route('conference.join.form', $conference))
            ->post(route('conference.join.attempt', $conference), [
                'credential' => 'ana@student.com',
                'password' => 'student-pass',
            ]);

        $response->assertRedirect(route('conference.join.form', $conference));
        $response->assertSessionHasErrors('credential');
        $this->assertGuest('student');
    }

    public function test_other_teacher_cannot_open_someone_elses_room(): void
    {
        $owner = $this->createTeacher('teacher4@example.com');
        $otherTeacher = $this->createTeacher('teacher5@example.com');
        $section = Section::create([
            'name' => 'Jade',
            'grade_level' => 11,
            'teacher_id' => $owner->id,
        ]);

        $conference = VideoConference::create([
            'teacher_id' => $owner->id,
            'section_id' => $section->id,
            'title' => 'Private Session',
            'slug' => 'private-session',
            'is_active' => true,
        ]);

        $response = $this->actingAs($otherTeacher, 'teacher')->get(route('conference.room', $conference));

        $response->assertForbidden();
    }

    public function test_private_conference_requires_secret_key_for_student_join(): void
    {
        $teacher = $this->createTeacher('teacher6@example.com');
        $section = Section::create([
            'name' => 'Sampaguita',
            'grade_level' => 10,
            'teacher_id' => $teacher->id,
        ]);

        $conference = VideoConference::create([
            'teacher_id' => $teacher->id,
            'section_id' => $section->id,
            'title' => 'Private Room',
            'slug' => 'private-room-secret',
            'is_active' => true,
            'visibility' => 'private',
            'secret_key_hash' => Hash::make('SECRET12'),
        ]);

        Student::create([
            'lrn' => '123123123123',
            'first_name' => 'Marco',
            'last_name' => 'Reyes',
            'email' => 'marco@student.com',
            'password' => Hash::make('student-pass'),
            'section_id' => $section->id,
        ]);

        $missingKey = $this->from(route('conference.join.form', $conference))
            ->post(route('conference.join.attempt', $conference), [
                'credential' => 'marco@student.com',
                'password' => 'student-pass',
            ]);
        $missingKey->assertSessionHasErrors('secret_key');

        $badKey = $this->from(route('conference.join.form', $conference))
            ->post(route('conference.join.attempt', $conference), [
                'credential' => 'marco@student.com',
                'password' => 'student-pass',
                'secret_key' => 'WRONG123',
            ]);
        $badKey->assertSessionHasErrors('secret_key');

        $goodKey = $this->post(route('conference.join.attempt', $conference), [
            'credential' => 'marco@student.com',
            'password' => 'student-pass',
            'secret_key' => 'SECRET12',
        ]);
        $goodKey->assertRedirect(route('conference.room', $conference));
    }

    public function test_guest_can_join_private_conference_with_secret_key_and_temporary_name(): void
    {
        $teacher = $this->createTeacher('teacher7@example.com');

        $conference = VideoConference::create([
            'teacher_id' => $teacher->id,
            'section_id' => null,
            'title' => 'Guest Private Room',
            'slug' => 'guest-private-room',
            'is_active' => true,
            'visibility' => 'private',
            'secret_key_hash' => Hash::make('GUEST123'),
        ]);

        $validate = $this->post(route('conference.join.guest.validate', $conference), [
            'guest_secret_key' => 'GUEST123',
        ]);
        $validate->assertRedirect(route('conference.join.form', $conference));

        $join = $this->post(route('conference.join.guest', $conference), [
            'temporary_name' => 'Guest Parent',
        ]);
        $join->assertRedirect(route('conference.room', $conference));

        $room = $this->get(route('conference.room', $conference));
        $room->assertOk();
    }

    public function test_public_conference_allows_unassigned_student_to_join(): void
    {
        $teacher = $this->createTeacher('teacher8@example.com');
        $assignedSection = Section::create([
            'name' => 'Topaz',
            'grade_level' => 10,
            'teacher_id' => $teacher->id,
        ]);
        $otherSection = Section::create([
            'name' => 'Jasper',
            'grade_level' => 10,
        ]);

        $conference = VideoConference::create([
            'teacher_id' => $teacher->id,
            'section_id' => $assignedSection->id,
            'title' => 'Public Room',
            'slug' => 'public-room-open',
            'is_active' => true,
            'visibility' => 'public',
        ]);

        Student::create([
            'lrn' => '321321321321',
            'first_name' => 'Lia',
            'last_name' => 'Cruz',
            'email' => 'lia@student.com',
            'password' => Hash::make('student-pass'),
            'section_id' => $otherSection->id,
        ]);

        $response = $this->post(route('conference.join.attempt', $conference), [
            'credential' => 'lia@student.com',
            'password' => 'student-pass',
        ]);

        $response->assertRedirect(route('conference.room', $conference));
    }

    private function createTeacher(string $email): Teacher
    {
        return Teacher::create([
            'employee_id' => 'EMP-'.uniqid(),
            'first_name' => 'Test',
            'last_name' => 'Teacher',
            'email' => $email,
            'password' => Hash::make('password'),
            'department' => 'Science',
        ]);
    }
}
