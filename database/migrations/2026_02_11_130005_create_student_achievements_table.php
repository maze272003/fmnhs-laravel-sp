<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('achievement_id')->constrained('achievements')->cascadeOnDelete();
            $table->timestamp('completed_at');
            $table->integer('completion_count')->default(1); // For repeatable achievements
            $table->json('completion_data')->nullable(); // Additional data about completion
            $table->timestamps();

            $table->index(['student_id', 'achievement_id']);
            $table->index(['student_id', 'completed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_achievements');
    }
};
