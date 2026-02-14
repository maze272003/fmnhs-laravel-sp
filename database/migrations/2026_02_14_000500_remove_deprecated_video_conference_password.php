<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('video_conferences', function (Blueprint $table) {
            if (Schema::hasColumn('video_conferences', 'password')) {
                $table->dropColumn('password');
            }
        });
    }

    public function down(): void
    {
        Schema::table('video_conferences', function (Blueprint $table) {
            if (!Schema::hasColumn('video_conferences', 'password')) {
                $table->string('password')->nullable()->after('max_participants');
            }
        });
    }
};
