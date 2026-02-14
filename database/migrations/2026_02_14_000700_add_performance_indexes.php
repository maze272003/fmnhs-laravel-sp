<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration adds indexes for frequently filtered columns
     * to improve query performance.
     */
    public function up(): void
    {
        // Students table indexes
        Schema::table('students', function (Blueprint $table) {
            $table->index('section_id', 'students_section_id_index');
            $table->index('enrollment_status', 'students_enrollment_status_index');
            $table->index('grade_level', 'students_grade_level_index');
            $table->index('created_at', 'students_created_at_index');
        });

        // Teachers table indexes
        Schema::table('teachers', function (Blueprint $table) {
            $table->index('status', 'teachers_status_index');
            $table->index('department', 'teachers_department_index');
        });

        // Grades table indexes
        Schema::table('grades', function (Blueprint $table) {
            $table->index('student_id', 'grades_student_id_index');
            $table->index('subject_id', 'grades_subject_id_index');
            $table->index('teacher_id', 'grades_teacher_id_index');
            $table->index('quarter', 'grades_quarter_index');
            $table->index('school_year_id', 'grades_school_year_id_index');
        });

        // Attendances table indexes
        Schema::table('attendances', function (Blueprint $table) {
            $table->index('student_id', 'attendances_student_id_index');
            $table->index('subject_id', 'attendances_subject_id_index');
            $table->index('date', 'attendances_date_index');
            $table->index('status', 'attendances_status_index');
        });

        // Assignments table indexes
        Schema::table('assignments', function (Blueprint $table) {
            $table->index('teacher_id', 'assignments_teacher_id_index');
            $table->index('subject_id', 'assignments_subject_id_index');
            $table->index('section_id', 'assignments_section_id_index');
            $table->index('due_date', 'assignments_due_date_index');
        });

        // Submissions table indexes
        Schema::table('submissions', function (Blueprint $table) {
            $table->index('assignment_id', 'submissions_assignment_id_index');
            $table->index('student_id', 'submissions_student_id_index');
            $table->index('submitted_at', 'submissions_submitted_at_index');
        });

        // Announcements table indexes
        Schema::table('announcements', function (Blueprint $table) {
            $table->index('target_audience', 'announcements_target_audience_index');
            $table->index('created_at', 'announcements_created_at_index');
            $table->index('is_pinned', 'announcements_is_pinned_index');
        });

        // Schedules table indexes
        Schema::table('schedules', function (Blueprint $table) {
            $table->index('section_id', 'schedules_section_id_index');
            $table->index('teacher_id', 'schedules_teacher_id_index');
            $table->index('subject_id', 'schedules_subject_id_index');
            $table->index('day', 'schedules_day_index');
        });

        // Video conferences table indexes
        Schema::table('video_conferences', function (Blueprint $table) {
            $table->index('teacher_id', 'video_conferences_teacher_id_index');
            $table->index('section_id', 'video_conferences_section_id_index');
            $table->index('is_active', 'video_conferences_is_active_index');
            $table->index('started_at', 'video_conferences_started_at_index');
        });

        // Sections table indexes
        Schema::table('sections', function (Blueprint $table) {
            $table->index('teacher_id', 'sections_teacher_id_index');
            $table->index('grade_level', 'sections_grade_level_index');
        });

        // Progress reports table indexes
        Schema::table('progress_reports', function (Blueprint $table) {
            $table->index('student_id', 'progress_reports_student_id_index');
            $table->index('teacher_id', 'progress_reports_teacher_id_index');
            $table->index('created_at', 'progress_reports_created_at_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex('students_section_id_index');
            $table->dropIndex('students_enrollment_status_index');
            $table->dropIndex('students_grade_level_index');
            $table->dropIndex('students_created_at_index');
        });

        Schema::table('teachers', function (Blueprint $table) {
            $table->dropIndex('teachers_status_index');
            $table->dropIndex('teachers_department_index');
        });

        Schema::table('grades', function (Blueprint $table) {
            $table->dropIndex('grades_student_id_index');
            $table->dropIndex('grades_subject_id_index');
            $table->dropIndex('grades_teacher_id_index');
            $table->dropIndex('grades_quarter_index');
            $table->dropIndex('grades_school_year_id_index');
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex('attendances_student_id_index');
            $table->dropIndex('attendances_subject_id_index');
            $table->dropIndex('attendances_date_index');
            $table->dropIndex('attendances_status_index');
        });

        Schema::table('assignments', function (Blueprint $table) {
            $table->dropIndex('assignments_teacher_id_index');
            $table->dropIndex('assignments_subject_id_index');
            $table->dropIndex('assignments_section_id_index');
            $table->dropIndex('assignments_due_date_index');
        });

        Schema::table('submissions', function (Blueprint $table) {
            $table->dropIndex('submissions_assignment_id_index');
            $table->dropIndex('submissions_student_id_index');
            $table->dropIndex('submissions_submitted_at_index');
        });

        Schema::table('announcements', function (Blueprint $table) {
            $table->dropIndex('announcements_target_audience_index');
            $table->dropIndex('announcements_created_at_index');
            $table->dropIndex('announcements_is_pinned_index');
        });

        Schema::table('schedules', function (Blueprint $table) {
            $table->dropIndex('schedules_section_id_index');
            $table->dropIndex('schedules_teacher_id_index');
            $table->dropIndex('schedules_subject_id_index');
            $table->dropIndex('schedules_day_index');
        });

        Schema::table('video_conferences', function (Blueprint $table) {
            $table->dropIndex('video_conferences_teacher_id_index');
            $table->dropIndex('video_conferences_section_id_index');
            $table->dropIndex('video_conferences_is_active_index');
            $table->dropIndex('video_conferences_started_at_index');
        });

        Schema::table('sections', function (Blueprint $table) {
            $table->dropIndex('sections_teacher_id_index');
            $table->dropIndex('sections_grade_level_index');
        });

        Schema::table('progress_reports', function (Blueprint $table) {
            $table->dropIndex('progress_reports_student_id_index');
            $table->dropIndex('progress_reports_teacher_id_index');
            $table->dropIndex('progress_reports_created_at_index');
        });
    }
};
