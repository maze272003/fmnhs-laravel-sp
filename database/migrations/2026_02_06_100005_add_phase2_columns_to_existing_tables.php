<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Students Table
        Schema::table('students', function (Blueprint $table) {
            if (!Schema::hasColumn('students', 'enrollment_status')) {
                $table->string('enrollment_status')->default('Enrolled')->after('enrollment_type');
            }
            if (!Schema::hasColumn('students', 'is_alumni')) {
                $table->boolean('is_alumni')->default(false)->after('enrollment_status');
            }
        });

        // 2. Grades Table (FIXED)
        Schema::table('grades', function (Blueprint $table) {
            // FIX: Changed 'after' to point to 'school_year_id' (the FK), not 'school_year'
            if (!Schema::hasColumn('grades', 'is_locked')) {
                $table->boolean('is_locked')->default(false)->after('school_year_id');
            }
            if (!Schema::hasColumn('grades', 'locked_at')) {
                $table->timestamp('locked_at')->nullable()->after('is_locked');
            }
            if (!Schema::hasColumn('grades', 'locked_by')) {
                $table->string('locked_by')->nullable()->after('locked_at');
            }
        });

        // 3. Schedules Table (Updated to use Foreign Key)
        Schema::table('schedules', function (Blueprint $table) {
            // Use ID instead of string for consistency
            if (!Schema::hasColumn('schedules', 'school_year_id')) {
                $table->foreignId('school_year_id')
                      ->nullable()
                      ->after('room')
                      ->constrained('school_year_configs')
                      ->onDelete('set null');
            }
            
            // Add Room FK
            if (!Schema::hasColumn('schedules', 'room_id')) {
                $table->foreignId('room_id')
                      ->nullable()
                      ->after('room') // or after school_year_id
                      ->constrained('rooms')
                      ->onDelete('set null');
            }
        });

        // 4. Sections Table (Updated to use Foreign Key)
        Schema::table('sections', function (Blueprint $table) {
            if (!Schema::hasColumn('sections', 'school_year_id')) {
                $table->foreignId('school_year_id')
                      ->nullable()
                      ->after('strand')
                      ->constrained('school_year_configs')
                      ->onDelete('set null');
            }
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
            // Drop Foreign Keys
            $table->dropForeign(['room_id']);
            $table->dropForeign(['school_year_id']);
            // Drop Columns
            $table->dropColumn(['school_year_id', 'room_id']);
        });

        Schema::table('sections', function (Blueprint $table) {
            $table->dropForeign(['school_year_id']);
            $table->dropColumn('school_year_id');
        });
    }
};