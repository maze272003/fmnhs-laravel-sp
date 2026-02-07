<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Promotion history for auditing student level changes (Requirement #7)
        Schema::create('promotion_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            
            // FIX: Changed from integer to string to support "Alumni"
            $table->string('from_grade_level');
            $table->string('to_grade_level');
            
            $table->string('from_school_year');
            $table->string('to_school_year');
            $table->unsignedBigInteger('from_section_id')->nullable();
            $table->unsignedBigInteger('to_section_id')->nullable();
            $table->string('promoted_by')->nullable();
            $table->timestamps();

            $table->foreign('from_section_id')->references('id')->on('sections')->onDelete('set null');
            $table->foreign('to_section_id')->references('id')->on('sections')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotion_histories');
    }
};