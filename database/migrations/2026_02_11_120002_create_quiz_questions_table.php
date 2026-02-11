<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained('quizzes')->cascadeOnDelete();
            $table->text('question');
            $table->string('type')->default('multiple_choice'); // multiple_choice, true_false, poll
            $table->json('options'); // array of answer options
            $table->json('correct_answers')->nullable(); // array of correct option indices (null for polls)
            $table->integer('points')->default(1);
            $table->integer('time_limit')->nullable(); // seconds, overrides quiz default
            $table->integer('order')->default(0);
            $table->string('image_path')->nullable();
            $table->text('explanation')->nullable(); // explanation shown after answering
            $table->timestamps();

            $table->index(['quiz_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_questions');
    }
};
