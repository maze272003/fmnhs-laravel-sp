<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration adds composite unique constraints to prevent duplicate data
     * and ensure data integrity across the system.
     */
    public function up(): void
    {
        // Submissions: one submission per student per assignment
        Schema::table('submissions', function (Blueprint $table) {
            $table->unique(['assignment_id', 'student_id'], 'submissions_assignment_student_unique');
        });

        // Attendances: one attendance record per student per subject per date
        Schema::table('attendances', function (Blueprint $table) {
            $table->unique(['student_id', 'subject_id', 'date'], 'attendances_student_subject_date_unique');
        });

        // Grades: one grade per student per subject per teacher per quarter per school year
        Schema::table('grades', function (Blueprint $table) {
            $table->unique(
                ['student_id', 'subject_id', 'teacher_id', 'quarter', 'school_year_id'],
                'grades_student_subject_teacher_quarter_year_unique'
            );
        });

        // Parent_Student: one relationship per parent-student pair
        Schema::table('parent_student', function (Blueprint $table) {
            $table->unique(['parent_id', 'student_id'], 'parent_student_parent_student_unique');
        });

        // Study_group_members: one membership per student per group
        Schema::table('study_group_members', function (Blueprint $table) {
            $table->unique(['study_group_id', 'student_id'], 'study_group_members_group_student_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropUnique('submissions_assignment_student_unique');
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropUnique('attendances_student_subject_date_unique');
        });

        Schema::table('grades', function (Blueprint $table) {
            $table->dropUnique('grades_student_subject_teacher_quarter_year_unique');
        });

        Schema::table('parent_student', function (Blueprint $table) {
            $table->dropUnique('parent_student_parent_student_unique');
        });

        Schema::table('study_group_members', function (Blueprint $table) {
            $table->dropUnique('study_group_members_group_student_unique');
        });
    }
};
