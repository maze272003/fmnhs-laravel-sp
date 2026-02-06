<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add enrollment_status and is_alumni to students
        Schema::table('students', function (Blueprint $table) {
            $table->string('enrollment_status')->default('Enrolled')
                  ->after('enrollment_type');
            // Enrolled, Promoted, Transferee, Dropped, Archived, Alumni
            $table->boolean('is_alumni')->default(false)->after('enrollment_status');
        });

        // Add is_locked to grades for grade locking
        Schema::table('grades', function (Blueprint $table) {
            $table->boolean('is_locked')->default(false)->after('school_year');
            $table->timestamp('locked_at')->nullable()->after('is_locked');
            $table->string('locked_by')->nullable()->after('locked_at');
        });

        // Add school_year to schedules for school-year-based scheduling
        Schema::table('schedules', function (Blueprint $table) {
            $table->string('school_year')->nullable()->after('room');
        });

        // Add school_year to sections
        Schema::table('sections', function (Blueprint $table) {
            $table->string('school_year')->nullable()->after('strand');
        });

        // Add room_id FK to schedules (linking to rooms table)
        Schema::table('schedules', function (Blueprint $table) {
            $table->foreignId('room_id')->nullable()->after('room')
                  ->constrained('rooms')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['enrollment_status', 'is_alumni']);
        });

        Schema::table('grades', function (Blueprint $table) {
            $table->dropColumn(['is_locked', 'locked_at', 'locked_by']);
        });

        Schema::table('schedules', function (Blueprint $table) {
            $table->dropForeign(['room_id']);
            $table->dropColumn(['school_year', 'room_id']);
        });

        Schema::table('sections', function (Blueprint $table) {
            $table->dropColumn('school_year');
        });
    }
};
