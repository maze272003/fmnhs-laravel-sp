<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->nullable(); // Icon class or path
            $table->string('color')->default('blue'); // Badge color
            $table->string('category')->default('general'); // academic, attendance, participation, special
            $table->json('unlock_criteria'); // Conditions to unlock the badge
            $table->integer('points_value')->default(0); // Points awarded when earned
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['category', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('badges');
    }
};
