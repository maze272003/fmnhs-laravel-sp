<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whiteboard_elements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('whiteboard_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // pen/shape/text/image
            $table->json('data');
            $table->integer('layer')->default(0);
            $table->string('created_by_type');
            $table->unsignedBigInteger('created_by_id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whiteboard_elements');
    }
};
