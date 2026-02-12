<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('presentations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conference_id')->nullable()->constrained('video_conferences')->nullOnDelete();
            $table->string('title');
            $table->string('file_path');
            $table->integer('slide_count')->default(0);
            $table->string('created_by_type');
            $table->unsignedBigInteger('created_by_id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presentations');
    }
};
