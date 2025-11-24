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
    Schema::create('grades', function (Blueprint $table) {
        $table->id();
        
        // Who is this grade for?
        $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
        
        // Who gave the grade?
        $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');
        
        // What subject?
        $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
        
        // What Grading Period? (1st, 2nd, 3rd, 4th)
        $table->integer('quarter'); 
        
        // The actual score
        $table->decimal('grade_value', 5, 2); // e.g., 89.50
        
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};
