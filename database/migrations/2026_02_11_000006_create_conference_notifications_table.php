<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conference_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conference_id')->constrained('video_conferences')->cascadeOnDelete();
            $table->string('recipient_type'); // teacher, student
            $table->unsignedBigInteger('recipient_id');
            $table->string('type'); // reminder, join_alert, network_warning, digest, missed, speaker_attention
            $table->string('channel')->default('web'); // web, email
            $table->text('message');
            $table->json('metadata')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['recipient_type', 'recipient_id', 'is_read'], 'conf_notif_recipient_read_idx');
            $table->index(['conference_id', 'type'], 'conf_notif_conference_type_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conference_notifications');
    }
};
