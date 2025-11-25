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
    Schema::create('submissions', function (Blueprint $table) {
        $table->id();
        $table->foreignId('assignment_id')->constrained('assignments')->onDelete('cascade');
        $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
        $table->string('file_path'); // Student's homework file
        $table->text('remarks')->nullable(); // Optional student note
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};
