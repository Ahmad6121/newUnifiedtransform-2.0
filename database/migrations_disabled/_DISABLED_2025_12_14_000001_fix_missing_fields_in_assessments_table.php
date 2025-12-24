<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('assessments')) return;

        Schema::table('assessments', function (Blueprint $table) {

            // online settings
            if (!Schema::hasColumn('assessments', 'is_randomized')) {
                $table->boolean('is_randomized')->default(false)->after('duration_minutes');
            }

            if (!Schema::hasColumn('assessments', 'attempts_allowed')) {
                $table->unsignedInteger('attempts_allowed')->default(1)->after('is_randomized');
            }

            // status timestamps
            if (!Schema::hasColumn('assessments', 'published_at')) {
                $table->dateTime('published_at')->nullable()->after('status');
            }

            if (!Schema::hasColumn('assessments', 'closed_at')) {
                $table->dateTime('closed_at')->nullable()->after('published_at');
            }

            // results_published (إذا مش موجود)
            if (!Schema::hasColumn('assessments', 'results_published')) {
                $table->boolean('results_published')->default(false)->after('closed_at');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('assessments')) return;

        Schema::table('assessments', function (Blueprint $table) {
            if (Schema::hasColumn('assessments', 'results_published')) $table->dropColumn('results_published');
            if (Schema::hasColumn('assessments', 'closed_at')) $table->dropColumn('closed_at');
            if (Schema::hasColumn('assessments', 'published_at')) $table->dropColumn('published_at');
            if (Schema::hasColumn('assessments', 'attempts_allowed')) $table->dropColumn('attempts_allowed');
            if (Schema::hasColumn('assessments', 'is_randomized')) $table->dropColumn('is_randomized');
        });
    }
};
