<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained('quizzes')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('quiz_questions')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->json('selected_answers'); // array of selected option indices
            $table->boolean('is_correct')->nullable(); // null for polls
            $table->integer('points_earned')->default(0);
            $table->integer('time_taken')->nullable(); // seconds taken to answer
            $table->timestamps();

            $table->index(['quiz_id', 'student_id']);
            $table->index(['question_id', 'student_id']);
            $table->unique(['question_id', 'student_id']); // one response per student per question
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_responses');
    }
};
