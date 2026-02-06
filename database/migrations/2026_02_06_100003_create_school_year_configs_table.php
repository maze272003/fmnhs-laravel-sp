<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_year_configs', function (Blueprint $table) {
            $table->id();
            $table->string('school_year')->unique(); // e.g. 2024-2025
            $table->boolean('is_active')->default(false);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('status')->default('active'); // active, closed, archived
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_year_configs');
    }
};
