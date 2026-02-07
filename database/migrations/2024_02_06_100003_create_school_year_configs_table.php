<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_year_configs', function (Blueprint $table) {
            $table->id(); // This creates the 'id' column automatically.
            
            // CORRECT: Name this 'school_year' so it holds "2025-2026"
            // DO NOT name this 'school_year_id'
            $table->string('school_year')->unique(); 
            
            $table->boolean('is_active')->default(false);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('status')->default('active'); 
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_year_configs');
    }
};