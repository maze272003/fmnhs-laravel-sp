<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('intervention_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('alert_type');
            $table->string('severity')->default('medium');
            $table->text('description')->nullable();
            $table->json('data')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->string('resolved_by_type')->nullable();
            $table->unsignedBigInteger('resolved_by_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('intervention_alerts');
    }
};
