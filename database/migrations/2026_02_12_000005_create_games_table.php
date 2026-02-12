<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conference_id')->nullable()->constrained('video_conferences')->nullOnDelete();
            $table->string('type'); // bingo/wordcloud/hangman/memory
            $table->string('title');
            $table->json('settings')->nullable();
            $table->string('status')->default('pending');
            $table->unsignedBigInteger('created_by_id');
            $table->string('created_by_type');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
