<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conference_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conference_id')->constrained('video_conferences')->cascadeOnDelete();
            $table->string('actor_id');
            $table->string('display_name');
            $table->string('role')->default('participant');
            $table->text('content')->nullable();
            $table->string('type')->default('text'); // text, file, system, reaction
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->string('file_mime')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->integer('conference_elapsed_seconds')->default(0);
            $table->timestamps();

            $table->index(['conference_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conference_messages');
    }
};
