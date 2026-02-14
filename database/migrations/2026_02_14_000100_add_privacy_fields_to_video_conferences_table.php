<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('video_conferences', function (Blueprint $table) {
            $table->string('visibility', 20)->default('private')->after('is_active');
            $table->string('secret_key_hash')->nullable()->after('visibility');
            $table->timestamp('terminated_at')->nullable()->after('ended_at');
        });
    }

    public function down(): void
    {
        Schema::table('video_conferences', function (Blueprint $table) {
            $table->dropColumn(['visibility', 'secret_key_hash', 'terminated_at']);
        });
    }
};
