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
        $this->addIndexIfMissing('students', ['section_id'], 'students_section_id_index');
        $this->addIndexIfMissing('students', ['enrollment_status'], 'students_enrollment_status_index');
        $this->addIndexIfMissing('students', ['grade_level'], 'students_grade_level_index');
        $this->addIndexIfMissing('students', ['created_at'], 'students_created_at_index');

        // Teachers table indexes
        $this->addIndexIfMissing('teachers', ['status'], 'teachers_status_index');
        $this->addIndexIfMissing('teachers', ['department'], 'teachers_department_index');

        // Grades table indexes
        $this->addIndexIfMissing('grades', ['student_id'], 'grades_student_id_index');
        $this->addIndexIfMissing('grades', ['subject_id'], 'grades_subject_id_index');
        $this->addIndexIfMissing('grades', ['teacher_id'], 'grades_teacher_id_index');
        $this->addIndexIfMissing('grades', ['quarter'], 'grades_quarter_index');
        $this->addIndexIfMissing('grades', ['school_year_id'], 'grades_school_year_id_index');

        // Attendances table indexes
        $this->addIndexIfMissing('attendances', ['student_id'], 'attendances_student_id_index');
        $this->addIndexIfMissing('attendances', ['subject_id'], 'attendances_subject_id_index');
        $this->addIndexIfMissing('attendances', ['date'], 'attendances_date_index');
        $this->addIndexIfMissing('attendances', ['status'], 'attendances_status_index');

        // Assignments table indexes
        $this->addIndexIfMissing('assignments', ['teacher_id'], 'assignments_teacher_id_index');
        $this->addIndexIfMissing('assignments', ['subject_id'], 'assignments_subject_id_index');
        $this->addIndexIfMissing('assignments', ['section_id'], 'assignments_section_id_index');
        $this->addIndexIfMissing('assignments', ['due_date'], 'assignments_due_date_index');

        // Submissions table indexes
        $this->addIndexIfMissing('submissions', ['assignment_id'], 'submissions_assignment_id_index');
        $this->addIndexIfMissing('submissions', ['student_id'], 'submissions_student_id_index');
        $this->addIndexIfMissing('submissions', ['submitted_at'], 'submissions_submitted_at_index');

        // Announcements table indexes
        $this->addIndexIfMissing('announcements', ['target_audience'], 'announcements_target_audience_index');
        $this->addIndexIfMissing('announcements', ['created_at'], 'announcements_created_at_index');
        $this->addIndexIfMissing('announcements', ['is_pinned'], 'announcements_is_pinned_index');

        // Schedules table indexes
        $this->addIndexIfMissing('schedules', ['section_id'], 'schedules_section_id_index');
        $this->addIndexIfMissing('schedules', ['teacher_id'], 'schedules_teacher_id_index');
        $this->addIndexIfMissing('schedules', ['subject_id'], 'schedules_subject_id_index');
        $this->addIndexIfMissing('schedules', ['day'], 'schedules_day_index');

        // Video conferences table indexes
        $this->addIndexIfMissing('video_conferences', ['teacher_id'], 'video_conferences_teacher_id_index');
        $this->addIndexIfMissing('video_conferences', ['section_id'], 'video_conferences_section_id_index');
        $this->addIndexIfMissing('video_conferences', ['is_active'], 'video_conferences_is_active_index');
        $this->addIndexIfMissing('video_conferences', ['started_at'], 'video_conferences_started_at_index');

        // Sections table indexes
        $this->addIndexIfMissing('sections', ['teacher_id'], 'sections_teacher_id_index');
        $this->addIndexIfMissing('sections', ['grade_level'], 'sections_grade_level_index');

        // Progress reports table indexes
        $this->addIndexIfMissing('progress_reports', ['student_id'], 'progress_reports_student_id_index');
        $this->addIndexIfMissing('progress_reports', ['teacher_id'], 'progress_reports_teacher_id_index');
        $this->addIndexIfMissing('progress_reports', ['created_at'], 'progress_reports_created_at_index');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->dropIndexIfExists('students', 'students_section_id_index');
        $this->dropIndexIfExists('students', 'students_enrollment_status_index');
        $this->dropIndexIfExists('students', 'students_grade_level_index');
        $this->dropIndexIfExists('students', 'students_created_at_index');

        $this->dropIndexIfExists('teachers', 'teachers_status_index');
        $this->dropIndexIfExists('teachers', 'teachers_department_index');

        $this->dropIndexIfExists('grades', 'grades_student_id_index');
        $this->dropIndexIfExists('grades', 'grades_subject_id_index');
        $this->dropIndexIfExists('grades', 'grades_teacher_id_index');
        $this->dropIndexIfExists('grades', 'grades_quarter_index');
        $this->dropIndexIfExists('grades', 'grades_school_year_id_index');

        $this->dropIndexIfExists('attendances', 'attendances_student_id_index');
        $this->dropIndexIfExists('attendances', 'attendances_subject_id_index');
        $this->dropIndexIfExists('attendances', 'attendances_date_index');
        $this->dropIndexIfExists('attendances', 'attendances_status_index');

        $this->dropIndexIfExists('assignments', 'assignments_teacher_id_index');
        $this->dropIndexIfExists('assignments', 'assignments_subject_id_index');
        $this->dropIndexIfExists('assignments', 'assignments_section_id_index');
        $this->dropIndexIfExists('assignments', 'assignments_due_date_index');

        $this->dropIndexIfExists('submissions', 'submissions_assignment_id_index');
        $this->dropIndexIfExists('submissions', 'submissions_student_id_index');
        $this->dropIndexIfExists('submissions', 'submissions_submitted_at_index');

        $this->dropIndexIfExists('announcements', 'announcements_target_audience_index');
        $this->dropIndexIfExists('announcements', 'announcements_created_at_index');
        $this->dropIndexIfExists('announcements', 'announcements_is_pinned_index');

        $this->dropIndexIfExists('schedules', 'schedules_section_id_index');
        $this->dropIndexIfExists('schedules', 'schedules_teacher_id_index');
        $this->dropIndexIfExists('schedules', 'schedules_subject_id_index');
        $this->dropIndexIfExists('schedules', 'schedules_day_index');

        $this->dropIndexIfExists('video_conferences', 'video_conferences_teacher_id_index');
        $this->dropIndexIfExists('video_conferences', 'video_conferences_section_id_index');
        $this->dropIndexIfExists('video_conferences', 'video_conferences_is_active_index');
        $this->dropIndexIfExists('video_conferences', 'video_conferences_started_at_index');

        $this->dropIndexIfExists('sections', 'sections_teacher_id_index');
        $this->dropIndexIfExists('sections', 'sections_grade_level_index');

        $this->dropIndexIfExists('progress_reports', 'progress_reports_student_id_index');
        $this->dropIndexIfExists('progress_reports', 'progress_reports_teacher_id_index');
        $this->dropIndexIfExists('progress_reports', 'progress_reports_created_at_index');
    }

    private function addIndexIfMissing(string $tableName, array $columns, string $indexName): void
    {
        if (! Schema::hasTable($tableName)) {
            return;
        }

        foreach ($columns as $column) {
            if (! Schema::hasColumn($tableName, $column)) {
                return;
            }
        }

        if (Schema::hasIndex($tableName, $indexName) || Schema::hasIndex($tableName, $columns)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($columns, $indexName) {
            $table->index($columns, $indexName);
        });
    }

    private function dropIndexIfExists(string $tableName, string $indexName): void
    {
        if (! Schema::hasTable($tableName)) {
            return;
        }

        if (! Schema::hasIndex($tableName, $indexName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($indexName) {
            $table->dropIndex($indexName);
        });
    }
};
