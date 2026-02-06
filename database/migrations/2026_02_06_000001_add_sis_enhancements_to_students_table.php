<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            // Soft deletes for archive mechanism (Requirement #2)
            $table->softDeletes();

            // Enrollment type: Regular or Transferee (Requirement #3)
            $table->string('enrollment_type')->default('Regular');

            // School year for versioned records (Requirement #5)
            $table->string('school_year')->default('2024-2025');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn(['enrollment_type', 'school_year']);
        });
    }
};
