<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            if (!Schema::hasColumn('schedules', 'room_id')) {
                $table->unsignedBigInteger('room_id')->nullable()->after('room');
                $table->foreign('room_id')->references('id')->on('rooms')->onDelete('set null');
            }

            if (!Schema::hasColumn('schedules', 'school_year_id')) {
                $table->unsignedBigInteger('school_year_id')->nullable()->after('room_id');
                $table->foreign('school_year_id')->references('id')->on('school_year_configs')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            if (Schema::hasColumn('schedules', 'school_year_id')) {
                $table->dropForeign(['school_year_id']);
                $table->dropColumn('school_year_id');
            }

            if (Schema::hasColumn('schedules', 'room_id')) {
                $table->dropForeign(['room_id']);
                $table->dropColumn('room_id');
            }
        });
    }
};
