<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whiteboards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conference_id')->constrained('video_conferences')->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->json('session_data')->nullable();
            $table->string('created_by_type');
            $table->unsignedBigInteger('created_by_id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whiteboards');
    }
};
