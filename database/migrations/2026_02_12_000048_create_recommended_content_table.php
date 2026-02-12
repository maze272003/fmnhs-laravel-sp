<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recommended_content', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('title');
            $table->string('type');
            $table->string('url')->nullable();
            $table->string('source')->nullable();
            $table->decimal('relevance_score', 5, 2)->default(0);
            $table->boolean('is_viewed')->default(false);
            $table->string('feedback')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recommended_content');
    }
};
