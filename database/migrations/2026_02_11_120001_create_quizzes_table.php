<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conference_id')->nullable()->constrained('video_conferences')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('type')->default('quiz'); // quiz, poll, survey
            $table->string('status')->default('draft'); // draft, active, completed, archived
            $table->integer('time_limit')->nullable(); // seconds per question, null for no limit
            $table->boolean('show_correct_answers')->default(true);
            $table->boolean('show_leaderboard')->default(true);
            $table->boolean('randomize_questions')->default(false);
            $table->boolean('randomize_options')->default(false);
            $table->integer('passing_score')->nullable(); // percentage, null for no passing score
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->json('settings')->nullable(); // additional settings
            $table->timestamps();

            $table->index(['conference_id', 'status']);
            $table->index(['teacher_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quizzes');
    }
};
