<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('assessment_results', function (Blueprint $table) {
            if (!Schema::hasColumn('assessment_results', 'graded_by')) {
                $table->unsignedBigInteger('graded_by')->nullable()->after('marks_obtained');

                // اختياري: FK لو عندك users
                // $table->foreign('graded_by')->references('id')->on('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('assessment_results', function (Blueprint $table) {
            if (Schema::hasColumn('assessment_results', 'graded_by')) {
                // اختياري لو عملت FK
                // $table->dropForeign(['graded_by']);
                $table->dropColumn('graded_by');
            }
        });
    }
};
