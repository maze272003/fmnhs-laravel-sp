<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        try {
            Schema::table('submissions', function (Blueprint $table) {
                if (Schema::hasColumn('submissions', 'assignment_id') && Schema::hasColumn('submissions', 'student_id')) {
                    $table->unique(['assignment_id', 'student_id'], 'submissions_assignment_student_unique');
                }
            });
        } catch (\Exception $e) {
            // Constraint may already exist
        }

        try {
            Schema::table('attendances', function (Blueprint $table) {
                if (Schema::hasColumn('attendances', 'student_id') && Schema::hasColumn('attendances', 'subject_id') && Schema::hasColumn('attendances', 'date')) {
                    $table->unique(['student_id', 'subject_id', 'date'], 'attendances_student_subject_date_unique');
                }
            });
        } catch (\Exception $e) {
            // Constraint may already exist
        }

        try {
            Schema::table('grades', function (Blueprint $table) {
                $columns = ['student_id', 'subject_id', 'teacher_id', 'quarter'];
                $hasAll = true;
                foreach ($columns as $col) {
                    if (!Schema::hasColumn('grades', $col)) {
                        $hasAll = false;
                        break;
                    }
                }

                if ($hasAll) {
                    if (Schema::hasColumn('grades', 'school_year_id')) {
                        $table->unique(['student_id', 'subject_id', 'teacher_id', 'quarter', 'school_year_id'], 'grades_composite_unique');
                    } else {
                        $table->unique(['student_id', 'subject_id', 'teacher_id', 'quarter', 'school_year'], 'grades_composite_unique');
                    }
                }
            });
        } catch (\Exception $e) {
            // Constraint may already exist
        }

        try {
            Schema::table('parent_student', function (Blueprint $table) {
                if (Schema::hasColumn('parent_student', 'parent_id') && Schema::hasColumn('parent_student', 'student_id')) {
                    $table->unique(['parent_id', 'student_id'], 'parent_student_unique');
                }
            });
        } catch (\Exception $e) {
            // Constraint may already exist
        }

        try {
            Schema::table('study_group_members', function (Blueprint $table) {
                if (Schema::hasColumn('study_group_members', 'study_group_id') && Schema::hasColumn('study_group_members', 'student_id')) {
                    $table->unique(['study_group_id', 'student_id'], 'study_group_members_group_student_unique');
                }
            });
        } catch (\Exception $e) {
            // Constraint may already exist
        }
    }

    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropUnique('submissions_assignment_student_unique');
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropUnique('attendances_student_subject_date_unique');
        });

        Schema::table('grades', function (Blueprint $table) {
            $table->dropUnique('grades_composite_unique');
        });

        Schema::table('parent_student', function (Blueprint $table) {
            $table->dropUnique('parent_student_unique');
        });

        Schema::table('study_group_members', function (Blueprint $table) {
            $table->dropUnique('study_group_members_group_student_unique');
        });
    }
};
