<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_badges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('badge_id')->constrained('badges')->cascadeOnDelete();
            $table->timestamp('earned_at');
            $table->text('note')->nullable(); // Optional note about how it was earned
            $table->timestamps();

            $table->unique(['student_id', 'badge_id']); // Each badge can only be earned once
            $table->index(['student_id', 'earned_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_badges');
    }
};
