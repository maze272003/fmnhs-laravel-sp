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
    Schema::table('submissions', function (Blueprint $table) {
        // Idagdag ang timestamp column pagkatapos ng file_path
        $table->timestamp('submitted_at')->nullable()->after('file_path');
    });
}

public function down(): void
{
    Schema::table('submissions', function (Blueprint $table) {
        $table->dropColumn('submitted_at');
    });
}
};
