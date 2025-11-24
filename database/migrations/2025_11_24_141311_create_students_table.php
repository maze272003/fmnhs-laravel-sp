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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            // LRN is usually 12 digits
            $table->string('lrn', 12)->unique(); 
            $table->string('first_name');
            $table->string('last_name');
            // Optional ang email sa bata, minsan wala pa silang email
            $table->string('email')->nullable()->unique(); 
            $table->string('password');
            // Grade 7 to 12
            $table->integer('grade_level'); 
            
            // Nullable kasi walang strand ang JHS (Gr 7-10)
            $table->string('strand')->nullable(); 
            
            // Section name (e.g., Rizal, Bonifacio, St. Paul)
            $table->string('section'); 

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
