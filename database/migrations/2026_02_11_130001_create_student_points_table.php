<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('source_type'); // quiz, attendance, participation, assignment
            $table->unsignedBigInteger('source_id')->nullable(); // ID of the quiz, attendance record, etc.
            $table->integer('points');
            $table->string('reason'); // Brief description
            $table->text('details')->nullable(); // Additional context
            $table->timestamps();

            $table->index(['student_id', 'created_at']);
            $table->index(['source_type', 'source_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_points');
    }
};
