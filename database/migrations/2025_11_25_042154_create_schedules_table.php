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
    Schema::create('schedules', function (Blueprint $table) {
        $table->id();
        // Who is this schedule for? (Linked by Section)
        $table->string('section'); 
        
        // What subject?
        $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
        
        // Who is teaching?
        $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');
        
        // When and Where?
        $table->string('day'); // e.g., "Monday", "MWF"
        $table->time('start_time');
        $table->time('end_time');
        $table->string('room')->nullable(); // e.g., "Rm 101"
        
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
