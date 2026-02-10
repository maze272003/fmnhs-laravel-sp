<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conference_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conference_id')->constrained('video_conferences')->cascadeOnDelete();
            $table->string('actor_id');
            $table->string('actor_type');
            $table->string('display_name');
            $table->string('role')->default('participant');
            $table->timestamp('joined_at')->nullable();
            $table->timestamp('left_at')->nullable();
            $table->integer('duration_seconds')->default(0);
            $table->boolean('is_guest')->default(false);
            $table->json('device_info')->nullable();
            $table->timestamps();

            $table->index(['conference_id', 'actor_id']);
            $table->index(['conference_id', 'joined_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conference_participants');
    }
};
