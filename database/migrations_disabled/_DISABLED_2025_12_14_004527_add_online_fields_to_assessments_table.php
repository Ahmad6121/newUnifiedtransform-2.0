<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('assessments')) return;

        Schema::table('assessments', function (Blueprint $table) {

            if (!Schema::hasColumn('assessments', 'duration_minutes')) {
                $table->unsignedInteger('duration_minutes')->nullable();
            }

            if (!Schema::hasColumn('assessments', 'attempts_allowed')) {
                $table->unsignedInteger('attempts_allowed')->default(1);
            }

            if (!Schema::hasColumn('assessments', 'is_randomized')) {
                $table->boolean('is_randomized')->default(false);
            }

            // إذا عندك أسماء مختلفة للتاريخ (start_at/end_at) أو مش موجودة
            if (!Schema::hasColumn('assessments', 'start_date')) {
                $table->dateTime('start_date')->nullable();
            }

            if (!Schema::hasColumn('assessments', 'end_date')) {
                $table->dateTime('end_date')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('assessments')) return;

        Schema::table('assessments', function (Blueprint $table) {
            if (Schema::hasColumn('assessments', 'is_randomized')) {
                $table->dropColumn('is_randomized');
            }
            if (Schema::hasColumn('assessments', 'attempts_allowed')) {
                $table->dropColumn('attempts_allowed');
            }
            if (Schema::hasColumn('assessments', 'duration_minutes')) {
                $table->dropColumn('duration_minutes');
            }
            // start_date/end_date عادة لا ننصح نحذفهم لو صار عليهم بيانات، لكن خليتهم هون حسب طلب الرجوع
            if (Schema::hasColumn('assessments', 'start_date')) {
                $table->dropColumn('start_date');
            }
            if (Schema::hasColumn('assessments', 'end_date')) {
                $table->dropColumn('end_date');
            }
        });
    }
};

