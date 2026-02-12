<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forum_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('thread_id')->constrained('forum_threads')->cascadeOnDelete();
            $table->text('body');
            $table->string('author_type');
            $table->unsignedBigInteger('author_id');
            $table->foreignId('parent_id')->nullable()->constrained('forum_posts')->nullOnDelete();
            $table->boolean('is_solution')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forum_posts');
    }
};
