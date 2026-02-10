<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conference_recordings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conference_id')->constrained('video_conferences')->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->string('disk')->default('s3');
            $table->string('file_path');
            $table->string('file_name');
            $table->string('mime_type')->default('video/webm');
            $table->unsignedBigInteger('file_size')->default(0);
            $table->integer('duration_seconds')->default(0);
            $table->string('type')->default('video'); // video, audio
            $table->string('status')->default('processing'); // processing, ready, failed
            $table->json('chapters')->nullable();
            $table->string('transcript_path')->nullable();
            $table->boolean('restricted')->default(true);
            $table->timestamp('ready_at')->nullable();
            $table->timestamps();

            $table->index(['conference_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conference_recordings');
    }
};
