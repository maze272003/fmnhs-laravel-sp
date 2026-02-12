<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('slide_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('slide_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->timestamp('viewed_at');
            $table->integer('duration_seconds')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('slide_views');
    }
};
