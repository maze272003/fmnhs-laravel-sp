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
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., Rizal, Bonifacio, Diamond
            $table->integer('grade_level'); // 7, 8, 9, 10, 11, or 12
            $table->string('strand')->nullable(); // e.g., STEM, ABM, HUMSS (null for JHS)
            
            // This assigns the Teacher as the Advisor for this specific section
            $table->foreignId('teacher_id')
                  ->nullable() 
                  ->constrained('teachers')
                  ->onDelete('set null'); // If teacher is deleted, section remains but has no advisor
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sections');
    }
};