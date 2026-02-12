<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('captions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conference_id')->constrained('video_conferences')->cascadeOnDelete();
            $table->text('text');
            $table->string('language')->default('en');
            $table->string('speaker_type')->nullable();
            $table->unsignedBigInteger('speaker_id')->nullable();
            $table->unsignedBigInteger('timestamp_ms');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('captions');
    }
};
