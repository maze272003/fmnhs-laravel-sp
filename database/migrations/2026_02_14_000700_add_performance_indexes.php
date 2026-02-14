<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private function indexExists(string $table, string $indexName): bool
    {
        $db = DB::getDatabaseName();

        return DB::table('information_schema.STATISTICS')
            ->where('TABLE_SCHEMA', $db)
            ->where('TABLE_NAME', $table)
            ->where('INDEX_NAME', $indexName)
            ->exists();
    }

    private function addIndexIfExists(string $table, string $column, string $indexName): void
    {
        if (!Schema::hasColumn($table, $column)) {
            return;
        }

        if ($this->indexExists($table, $indexName)) {
            return;
        }

        Schema::table($table, function (Blueprint $t) use ($column, $indexName) {
            $t->index($column, $indexName);
        });
    }

    private function dropIndexIfExists(string $table, string $indexName): void
    {
        if (!$this->indexExists($table, $indexName)) {
            return;
        }

        Schema::table($table, function (Blueprint $t) use ($indexName) {
            $t->dropIndex($indexName);
        });
    }

    public function up(): void
    {
        // Students
        $this->addIndexIfExists('students', 'section_id', 'students_section_id_index');
        $this->addIndexIfExists('students', 'enrollment_status', 'students_enrollment_status_index');
        $this->addIndexIfExists('students', 'grade_level', 'students_grade_level_index'); // will skip if missing
        $this->addIndexIfExists('students', 'created_at', 'students_created_at_index');

        // Teachers
        $this->addIndexIfExists('teachers', 'status', 'teachers_status_index');
        $this->addIndexIfExists('teachers', 'department', 'teachers_department_index');

        // Grades
        $this->addIndexIfExists('grades', 'student_id', 'grades_student_id_index');
        $this->addIndexIfExists('grades', 'subject_id', 'grades_subject_id_index');
        $this->addIndexIfExists('grades', 'teacher_id', 'grades_teacher_id_index');
        $this->addIndexIfExists('grades', 'quarter', 'grades_quarter_index');
        $this->addIndexIfExists('grades', 'school_year_id', 'grades_school_year_id_index');

        // Attendances
        $this->addIndexIfExists('attendances', 'student_id', 'attendances_student_id_index');
        $this->addIndexIfExists('attendances', 'subject_id', 'attendances_subject_id_index');
        $this->addIndexIfExists('attendances', 'date', 'attendances_date_index');
        $this->addIndexIfExists('attendances', 'status', 'attendances_status_index');

        // Assignments
        $this->addIndexIfExists('assignments', 'teacher_id', 'assignments_teacher_id_index');
        $this->addIndexIfExists('assignments', 'subject_id', 'assignments_subject_id_index');
        $this->addIndexIfExists('assignments', 'section_id', 'assignments_section_id_index');
        $this->addIndexIfExists('assignments', 'due_date', 'assignments_due_date_index');

        // Submissions
        $this->addIndexIfExists('submissions', 'assignment_id', 'submissions_assignment_id_index');
        $this->addIndexIfExists('submissions', 'student_id', 'submissions_student_id_index');
        $this->addIndexIfExists('submissions', 'submitted_at', 'submissions_submitted_at_index');

        // Announcements
        $this->addIndexIfExists('announcements', 'target_audience', 'announcements_target_audience_index');
        $this->addIndexIfExists('announcements', 'created_at', 'announcements_created_at_index');
        $this->addIndexIfExists('announcements', 'is_pinned', 'announcements_is_pinned_index');

        // Schedules
        $this->addIndexIfExists('schedules', 'section_id', 'schedules_section_id_index');
        $this->addIndexIfExists('schedules', 'teacher_id', 'schedules_teacher_id_index');
        $this->addIndexIfExists('schedules', 'subject_id', 'schedules_subject_id_index');
        $this->addIndexIfExists('schedules', 'day', 'schedules_day_index');

        // Video conferences
        $this->addIndexIfExists('video_conferences', 'teacher_id', 'video_conferences_teacher_id_index');
        $this->addIndexIfExists('video_conferences', 'section_id', 'video_conferences_section_id_index');
        $this->addIndexIfExists('video_conferences', 'is_active', 'video_conferences_is_active_index');
        $this->addIndexIfExists('video_conferences', 'started_at', 'video_conferences_started_at_index');

        // Sections
        $this->addIndexIfExists('sections', 'teacher_id', 'sections_teacher_id_index');
        $this->addIndexIfExists('sections', 'grade_level', 'sections_grade_level_index');

        // Progress reports
        $this->addIndexIfExists('progress_reports', 'student_id', 'progress_reports_student_id_index');
        $this->addIndexIfExists('progress_reports', 'teacher_id', 'progress_reports_teacher_id_index');
        $this->addIndexIfExists('progress_reports', 'created_at', 'progress_reports_created_at_index');
    }

    public function down(): void
    {
        // Students
        $this->dropIndexIfExists('students', 'students_section_id_index');
        $this->dropIndexIfExists('students', 'students_enrollment_status_index');
        $this->dropIndexIfExists('students', 'students_grade_level_index');
        $this->dropIndexIfExists('students', 'students_created_at_index');

        // Teachers
        $this->dropIndexIfExists('teachers', 'teachers_status_index');
        $this->dropIndexIfExists('teachers', 'teachers_department_index');

        // Grades
        $this->dropIndexIfExists('grades', 'grades_student_id_index');
        $this->dropIndexIfExists('grades', 'grades_subject_id_index');
        $this->dropIndexIfExists('grades', 'grades_teacher_id_index');
        $this->dropIndexIfExists('grades', 'grades_quarter_index');
        $this->dropIndexIfExists('grades', 'grades_school_year_id_index');

        // Attendances
        $this->dropIndexIfExists('attendances', 'attendances_student_id_index');
        $this->dropIndexIfExists('attendances', 'attendances_subject_id_index');
        $this->dropIndexIfExists('attendances_date_index');
        $this->dropIndexIfExists('attendances_status_index');

        // Assignments
        $this->dropIndexIfExists('assignments', 'assignments_teacher_id_index');
        $this->dropIndexIfExists('assignments_subject_id_index');
        $this->dropIndexIfExists('assignments_section_id_index');
        $this->dropIndexIfExists('assignments_due_date_index');

        // Submissions
        $this->dropIndexIfExists('submissions', 'submissions_assignment_id_index');
        $this->dropIndexIfExists('submissions_student_id_index');
        $this->dropIndexIfExists('submissions_submitted_at_index');

        // Announcements
        $this->dropIndexIfExists('announcements', 'announcements_target_audience_index');
        $this->dropIndexIfExists('announcements_created_at_index');
        $this->dropIndexIfExists('announcements_is_pinned_index');

        // Schedules
        $this->dropIndexIfExists('schedules', 'schedules_section_id_index');
        $this->dropIndexIfExists('schedules_teacher_id_index');
        $this->dropIndexIfExists('schedules_subject_id_index');
        $this->dropIndexIfExists('schedules_day_index');

        // Video conferences
        $this->dropIndexIfExists('video_conferences', 'video_conferences_teacher_id_index');
        $this->dropIndexIfExists('video_conferences_section_id_index');
        $this->dropIndexIfExists('video_conferences_is_active_index');
        $this->dropIndexIfExists('video_conferences_started_at_index');

        // Sections
        $this->dropIndexIfExists('sections', 'sections_teacher_id_index');
        $this->dropIndexIfExists('sections_grade_level_index');

        // Progress reports
        $this->dropIndexIfExists('progress_reports', 'progress_reports_student_id_index');
        $this->dropIndexIfExists('progress_reports_teacher_id_index');
        $this->dropIndexIfExists('progress_reports_created_at_index');
    }
};
