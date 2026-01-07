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
        $table->id(); // Ito ay BigInt Unsigned
        $table->string('employee_id')->unique();
        $table->string('first_name');
        $table->string('last_name');
        $table->string('email')->unique();
        $table->string('password');
        $table->string('department');
        $table->softDeletes();
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
