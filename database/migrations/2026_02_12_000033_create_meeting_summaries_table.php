<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meeting_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conference_id')->constrained('video_conferences')->cascadeOnDelete();
            $table->text('summary')->nullable();
            $table->json('key_points')->nullable();
            $table->json('action_items')->nullable();
            $table->longText('transcript')->nullable();
            $table->string('generated_by')->default('ai');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meeting_summaries');
    }
};
