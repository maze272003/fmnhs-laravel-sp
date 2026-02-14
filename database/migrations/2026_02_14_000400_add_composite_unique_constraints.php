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

        $count = DB::table('information_schema.STATISTICS')
            ->where('TABLE_SCHEMA', $db)
            ->where('TABLE_NAME', $table)
            ->where('INDEX_NAME', $indexName)
            ->count();

        return $count > 0;
    }

    public function up(): void
    {
        // submissions: (assignment_id, student_id)
        Schema::table('submissions', function (Blueprint $table) {
            if (
                Schema::hasColumn('submissions', 'assignment_id') &&
                Schema::hasColumn('submissions', 'student_id')
            ) {
                // handled outside closure
            }
        });

        if (
            Schema::hasColumn('submissions', 'assignment_id') &&
            Schema::hasColumn('submissions', 'student_id') &&
            !$this->indexExists('submissions', 'submissions_assignment_student_unique')
        ) {
            Schema::table('submissions', function (Blueprint $table) {
                $table->unique(['assignment_id', 'student_id'], 'submissions_assignment_student_unique');
            });
        }

        // attendances: (student_id, subject_id, date)
        if (
            Schema::hasColumn('attendances', 'student_id') &&
            Schema::hasColumn('attendances', 'subject_id') &&
            Schema::hasColumn('attendances', 'date') &&
            !$this->indexExists('attendances', 'attendances_student_subject_date_unique')
        ) {
            Schema::table('attendances', function (Blueprint $table) {
                $table->unique(['student_id', 'subject_id', 'date'], 'attendances_student_subject_date_unique');
            });
        }

        // grades: (student_id, subject_id, teacher_id, quarter, school_year_id|school_year)
        $hasBase =
            Schema::hasColumn('grades', 'student_id') &&
            Schema::hasColumn('grades', 'subject_id') &&
            Schema::hasColumn('grades', 'teacher_id') &&
            Schema::hasColumn('grades', 'quarter');

        if ($hasBase && !$this->indexExists('grades', 'grades_composite_unique')) {
            if (Schema::hasColumn('grades', 'school_year_id')) {
                Schema::table('grades', function (Blueprint $table) {
                    $table->unique(
                        ['student_id', 'subject_id', 'teacher_id', 'quarter', 'school_year_id'],
                        'grades_composite_unique'
                    );
                });
            } elseif (Schema::hasColumn('grades', 'school_year')) {
                Schema::table('grades', function (Blueprint $table) {
                    $table->unique(
                        ['student_id', 'subject_id', 'teacher_id', 'quarter', 'school_year'],
                        'grades_composite_unique'
                    );
                });
            }
        }

        // parent_student: (parent_id, student_id)
        if (
            Schema::hasColumn('parent_student', 'parent_id') &&
            Schema::hasColumn('parent_student', 'student_id') &&
            !$this->indexExists('parent_student', 'parent_student_unique')
        ) {
            Schema::table('parent_student', function (Blueprint $table) {
                $table->unique(['parent_id', 'student_id'], 'parent_student_unique');
            });
        }

        // study_group_members: (study_group_id, student_id)
        if (
            Schema::hasColumn('study_group_members', 'study_group_id') &&
            Schema::hasColumn('study_group_members', 'student_id') &&
            !$this->indexExists('study_group_members', 'study_group_members_group_student_unique')
        ) {
            Schema::table('study_group_members', function (Blueprint $table) {
                $table->unique(['study_group_id', 'student_id'], 'study_group_members_group_student_unique');
            });
        }
    }

    public function down(): void
    {
        if ($this->indexExists('submissions', 'submissions_assignment_student_unique')) {
            Schema::table('submissions', function (Blueprint $table) {
                $table->dropUnique('submissions_assignment_student_unique');
            });
        }

        if ($this->indexExists('attendances', 'attendances_student_subject_date_unique')) {
            Schema::table('attendances', function (Blueprint $table) {
                $table->dropUnique('attendances_student_subject_date_unique');
            });
        }

        if ($this->indexExists('grades', 'grades_composite_unique')) {
            Schema::table('grades', function (Blueprint $table) {
                $table->dropUnique('grades_composite_unique');
            });
        }

        if ($this->indexExists('parent_student', 'parent_student_unique')) {
            Schema::table('parent_student', function (Blueprint $table) {
                $table->dropUnique('parent_student_unique');
            });
        }

        if ($this->indexExists('study_group_members', 'study_group_members_group_student_unique')) {
            Schema::table('study_group_members', function (Blueprint $table) {
                $table->dropUnique('study_group_members_group_student_unique');
            });
        }
    }
};
