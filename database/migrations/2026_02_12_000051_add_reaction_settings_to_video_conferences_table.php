<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('video_conferences', function (Blueprint $table) {
            $table->json('reaction_settings')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('video_conferences', function (Blueprint $table) {
            $table->dropColumn('reaction_settings');
        });
    }
};
