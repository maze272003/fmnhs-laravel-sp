<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            // Allow section_id to be NULL for Alumni or Unenrolled students
            $table->foreignId('section_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            // Revert back to required (not null)
            // Note: This might fail if you have existing Alumni with null sections
            $table->foreignId('section_id')->nullable(false)->change();
        });
    }
};