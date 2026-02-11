<?php

namespace Tests\Feature;

use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizResponse;
use App\Models\Section;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\VideoConference;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class QuizTest extends TestCase
{
    use RefreshDatabase;

    protected function createTeacher(string $email): Teacher
    {
        return Teacher::create([
            'email' => $email,
            'first_name' => 'Test',
            'last_name' => 'Teacher',
            'employee_id' => 'T' . rand(10000, 99999),
            'department' => 'Test Department',
            'password' => Hash::make('password'),
        ]);
    }

    protected function createStudent(string $lrn, ?int $sectionId = null): Student
    {
        return Student::create([
            'lrn' => $lrn,
            'first_name' => 'Test',
            'last_name' => 'Student',
            'middle_name' => 'M',
            'email' => $lrn . '@example.com',
            'password' => Hash::make('password'),
            'grade_level' => 10,
            'section_id' => $sectionId,
        ]);
    }

    public function test_teacher_can_create_quiz(): void
    {
        $teacher = $this->createTeacher('teacher@test.com');
        
        $response = $this->actingAs($teacher, 'teacher')->postJson('/api/quizzes', [
            'title' => 'Math Quiz 1',
            'description' => 'First quarter math assessment',
            'type' => 'quiz',
            'time_limit' => 60,
            'show_correct_answers' => true,
            'show_leaderboard' => true,
            'passing_score' => 75,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('quizzes', [
            'teacher_id' => $teacher->id,
            'title' => 'Math Quiz 1',
            'type' => 'quiz',
            'status' => 'draft',
        ]);
    }

    public function test_teacher_can_add_question_to_quiz(): void
    {
        $teacher = $this->createTeacher('teacher@test.com');
        $quiz = Quiz::create([
            'teacher_id' => $teacher->id,
            'title' => 'Science Quiz',
            'type' => 'quiz',
        ]);

        $response = $this->actingAs($teacher, 'teacher')->postJson("/api/quizzes/{$quiz->id}/questions", [
            'question' => 'What is the powerhouse of the cell?',
            'type' => 'multiple_choice',
            'options' => ['Nucleus', 'Mitochondria', 'Ribosome', 'Golgi Apparatus'],
            'correct_answers' => [1],
            'points' => 2,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('quiz_questions', [
            'quiz_id' => $quiz->id,
            'question' => 'What is the powerhouse of the cell?',
            'type' => 'multiple_choice',
        ]);
    }

    public function test_teacher_can_start_quiz(): void
    {
        $teacher = $this->createTeacher('teacher@test.com');
        $quiz = Quiz::create([
            'teacher_id' => $teacher->id,
            'title' => 'History Quiz',
            'type' => 'quiz',
            'status' => 'draft',
        ]);

        $response = $this->actingAs($teacher, 'teacher')->postJson("/api/quizzes/{$quiz->id}/start");

        $response->assertStatus(200);
        $quiz->refresh();
        $this->assertEquals('active', $quiz->status);
        $this->assertNotNull($quiz->started_at);
    }

    public function test_student_can_submit_quiz_response(): void
    {
        $teacher = $this->createTeacher('teacher@test.com');
        $student = $this->createStudent('123456789012');
        
        $quiz = Quiz::create([
            'teacher_id' => $teacher->id,
            'title' => 'English Quiz',
            'type' => 'quiz',
            'status' => 'active',
        ]);

        $question = QuizQuestion::create([
            'quiz_id' => $quiz->id,
            'question' => 'What is a noun?',
            'type' => 'multiple_choice',
            'options' => ['A verb', 'A person, place or thing', 'An adjective', 'An adverb'],
            'correct_answers' => [1],
            'points' => 1,
        ]);

        $response = $this->actingAs($student, 'student')->postJson("/api/quizzes/{$quiz->id}/questions/{$question->id}/respond", [
            'selected_answers' => [1],
            'time_taken' => 15,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('quiz_responses', [
            'quiz_id' => $quiz->id,
            'question_id' => $question->id,
            'student_id' => $student->id,
            'is_correct' => true,
            'points_earned' => 1,
        ]);
    }

    public function test_quiz_leaderboard_shows_top_students(): void
    {
        $teacher = $this->createTeacher('teacher@test.com');
        $quiz = Quiz::create([
            'teacher_id' => $teacher->id,
            'title' => 'Math Quiz',
            'type' => 'quiz',
            'status' => 'active',
        ]);

        $question = QuizQuestion::create([
            'quiz_id' => $quiz->id,
            'question' => 'What is 2+2?',
            'type' => 'multiple_choice',
            'options' => ['3', '4', '5', '6'],
            'correct_answers' => [1],
            'points' => 10,
        ]);

        // Student 1 gets correct answer
        $student1 = $this->createStudent('111111111111');
        QuizResponse::create([
            'quiz_id' => $quiz->id,
            'question_id' => $question->id,
            'student_id' => $student1->id,
            'selected_answers' => [1],
            'is_correct' => true,
            'points_earned' => 10,
        ]);

        // Student 2 gets wrong answer
        $student2 = $this->createStudent('222222222222');
        QuizResponse::create([
            'quiz_id' => $quiz->id,
            'question_id' => $question->id,
            'student_id' => $student2->id,
            'selected_answers' => [0],
            'is_correct' => false,
            'points_earned' => 0,
        ]);

        $response = $this->actingAs($teacher, 'teacher')->getJson("/api/quizzes/{$quiz->id}/leaderboard");

        $response->assertStatus(200);
        $leaderboard = $response->json();
        
        $this->assertCount(2, $leaderboard);
        $this->assertEquals(10, $leaderboard[0]['total_points']);
        $this->assertEquals(0, $leaderboard[1]['total_points']);
    }

    public function test_poll_type_quiz_does_not_have_correct_answers(): void
    {
        $teacher = $this->createTeacher('teacher@test.com');
        $student = $this->createStudent('123456789012');
        
        $quiz = Quiz::create([
            'teacher_id' => $teacher->id,
            'title' => 'Opinion Poll',
            'type' => 'poll',
            'status' => 'active',
        ]);

        $question = QuizQuestion::create([
            'quiz_id' => $quiz->id,
            'question' => 'What is your favorite subject?',
            'type' => 'poll',
            'options' => ['Math', 'Science', 'English', 'History'],
            'correct_answers' => null,
            'points' => 0,
        ]);

        $response = $this->actingAs($student, 'student')->postJson("/api/quizzes/{$quiz->id}/questions/{$question->id}/respond", [
            'selected_answers' => [1],
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('quiz_responses', [
            'quiz_id' => $quiz->id,
            'question_id' => $question->id,
            'student_id' => $student->id,
            'is_correct' => null,
            'points_earned' => 0,
        ]);
    }

    public function test_quiz_can_be_linked_to_video_conference(): void
    {
        $teacher = $this->createTeacher('teacher@test.com');
        $section = Section::create([
            'name' => 'Test Section',
            'grade_level' => 10,
            'teacher_id' => $teacher->id,
        ]);

        $conference = VideoConference::create([
            'teacher_id' => $teacher->id,
            'section_id' => $section->id,
            'title' => 'Live Class',
            'slug' => 'live-class',
            'is_active' => true,
        ]);

        $response = $this->actingAs($teacher, 'teacher')->postJson('/api/quizzes', [
            'conference_id' => $conference->id,
            'title' => 'Live Quiz',
            'type' => 'quiz',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('quizzes', [
            'conference_id' => $conference->id,
            'title' => 'Live Quiz',
        ]);
    }

    public function test_student_cannot_submit_response_twice_to_same_question(): void
    {
        $teacher = $this->createTeacher('teacher@test.com');
        $student = $this->createStudent('123456789012');
        
        $quiz = Quiz::create([
            'teacher_id' => $teacher->id,
            'title' => 'Test Quiz',
            'type' => 'quiz',
            'status' => 'active',
        ]);

        $question = QuizQuestion::create([
            'quiz_id' => $quiz->id,
            'question' => 'Test question?',
            'type' => 'multiple_choice',
            'options' => ['A', 'B', 'C', 'D'],
            'correct_answers' => [0],
            'points' => 1,
        ]);

        // First submission
        $this->actingAs($student, 'student')->postJson("/api/quizzes/{$quiz->id}/questions/{$question->id}/respond", [
            'selected_answers' => [0],
        ]);

        // Second submission should update, not create new
        $this->actingAs($student, 'student')->postJson("/api/quizzes/{$quiz->id}/questions/{$question->id}/respond", [
            'selected_answers' => [1],
        ]);

        $responses = QuizResponse::where('question_id', $question->id)
            ->where('student_id', $student->id)
            ->count();

        $this->assertEquals(1, $responses);
    }
}
