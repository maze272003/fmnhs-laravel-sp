<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('assignments', function (Blueprint $table) {
        $table->id();
        $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');
        $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
        $table->string('section'); // Assignments are per section
        $table->string('title');
        $table->text('description')->nullable();
        $table->string('file_path')->nullable(); // Teacher's attachment
        $table->dateTime('deadline');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};
