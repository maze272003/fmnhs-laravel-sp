<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('teachers', function (Blueprint $table) {
        $table->id();
        $table->string('employee_id')->unique(); // ID ng teacher
        $table->string('first_name');
        $table->string('last_name');
        $table->string('email')->unique();
        $table->string('password'); // Required for login
        $table->string('department'); // e.g. Math, Science, English
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};
