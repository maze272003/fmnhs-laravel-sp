<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_grading_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained('submissions')->cascadeOnDelete();
            $table->decimal('ai_score', 5, 2)->nullable();
            $table->text('ai_feedback')->nullable();
            $table->decimal('human_score', 5, 2)->nullable();
            $table->json('rubric_data')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_grading_logs');
    }
};
