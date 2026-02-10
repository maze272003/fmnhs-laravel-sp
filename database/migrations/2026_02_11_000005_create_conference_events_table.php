<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conference_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conference_id')->constrained('video_conferences')->cascadeOnDelete();
            $table->string('actor_id')->nullable();
            $table->string('event_type'); // join, leave, mute, unmute, screen_share_start, screen_share_stop, raise_hand, recording_start, recording_stop, etc.
            $table->json('metadata')->nullable();
            $table->integer('conference_elapsed_seconds')->default(0);
            $table->timestamps();

            $table->index(['conference_id', 'event_type']);
            $table->index(['conference_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conference_events');
    }
};
