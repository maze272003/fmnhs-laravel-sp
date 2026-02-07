<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            // 1. Soft deletes for archive mechanism
            // (Checks if column exists first to prevent errors if re-running)
            if (!Schema::hasColumn('students', 'deleted_at')) {
                $table->softDeletes();
            }

            // 2. Enrollment type
            if (!Schema::hasColumn('students', 'enrollment_type')) {
                $table->string('enrollment_type')->default('Regular');
            }

            // 3. School year Foreign Key (The Normalization Fix)
            // We use 'school_year_id' to link to the 'school_year_configs' table
            if (!Schema::hasColumn('students', 'school_year_id')) {
                $table->foreignId('school_year_id')
                      ->nullable()
                      ->constrained('school_year_configs')
                      ->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            // Remove Soft Deletes
            $table->dropSoftDeletes();

            // Remove Enrollment Type
            $table->dropColumn('enrollment_type');

            // FIX: Drop the Foreign Key Constraint first, then the column
            // We must use the array syntax ['column_name'] to drop the index
            $table->dropForeign(['school_year_id']); 
            
            // FIX: Drop 'school_year_id', NOT 'school_year'
            $table->dropColumn('school_year_id'); 
        });
    }
};