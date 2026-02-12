<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('breakout_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conference_id')->constrained('video_conferences')->cascadeOnDelete();
            $table->string('name');
            $table->json('settings')->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('breakout_rooms');
    }
};
