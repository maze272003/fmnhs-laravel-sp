<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('video_conferences', function (Blueprint $table) {
            $table->json('settings')->nullable()->after('ended_at');
            $table->string('branding_logo')->nullable()->after('settings');
            $table->string('branding_color', 7)->nullable()->after('branding_logo');
            $table->boolean('recording_enabled')->default(false)->after('branding_color');
            $table->boolean('chat_enabled')->default(true)->after('recording_enabled');
            $table->boolean('screen_share_enabled')->default(true)->after('chat_enabled');
            $table->boolean('guest_access')->default(false)->after('screen_share_enabled');
            $table->integer('max_participants')->default(50)->after('guest_access');
            $table->string('password')->nullable()->after('max_participants');
            $table->json('notification_rules')->nullable()->after('password');
        });
    }

    public function down(): void
    {
        Schema::table('video_conferences', function (Blueprint $table) {
            $table->dropColumn([
                'settings', 'branding_logo', 'branding_color', 'recording_enabled',
                'chat_enabled', 'screen_share_enabled', 'guest_access',
                'max_participants', 'password', 'notification_rules',
            ]);
        });
    }
};
