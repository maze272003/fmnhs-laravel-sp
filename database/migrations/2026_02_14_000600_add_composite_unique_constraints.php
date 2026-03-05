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
        $this->addUniqueIfMissing(
            'submissions',
            ['assignment_id', 'student_id'],
            'submissions_assignment_student_unique'
        );

        // Attendances: one attendance record per student per subject per date
        $this->addUniqueIfMissing(
            'attendances',
            ['student_id', 'subject_id', 'date'],
            'attendances_student_subject_date_unique'
        );

        // Grades: one grade per student per subject per teacher per quarter per school year
        $this->addUniqueIfMissing(
            'grades',
            ['student_id', 'subject_id', 'teacher_id', 'quarter', 'school_year_id'],
            'grades_student_subject_teacher_quarter_year_unique'
        );

        // Parent_Student: one relationship per parent-student pair
        $this->addUniqueIfMissing(
            'parent_student',
            ['parent_id', 'student_id'],
            'parent_student_parent_student_unique'
        );

        // Study_group_members: one membership per student per group
        $this->addUniqueIfMissing(
            'study_group_members',
            ['study_group_id', 'student_id'],
            'study_group_members_group_student_unique'
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->dropUniqueIfExists('submissions', 'submissions_assignment_student_unique');
        $this->dropUniqueIfExists('attendances', 'attendances_student_subject_date_unique');
        $this->dropUniqueIfExists('grades', 'grades_student_subject_teacher_quarter_year_unique');
        $this->dropUniqueIfExists('parent_student', 'parent_student_parent_student_unique');
        $this->dropUniqueIfExists('study_group_members', 'study_group_members_group_student_unique');
    }

    private function addUniqueIfMissing(string $tableName, array $columns, string $indexName): void
    {
        if (! Schema::hasTable($tableName)) {
            return;
        }

        foreach ($columns as $column) {
            if (! Schema::hasColumn($tableName, $column)) {
                return;
            }
        }

        if (
            Schema::hasIndex($tableName, $indexName, 'unique')
            || Schema::hasIndex($tableName, $columns, 'unique')
        ) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($columns, $indexName) {
            $table->unique($columns, $indexName);
        });
    }

    private function dropUniqueIfExists(string $tableName, string $indexName): void
    {
        if (! Schema::hasTable($tableName)) {
            return;
        }

        if (! Schema::hasIndex($tableName, $indexName, 'unique')) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($indexName) {
            $table->dropUnique($indexName);
        });
    }
};
