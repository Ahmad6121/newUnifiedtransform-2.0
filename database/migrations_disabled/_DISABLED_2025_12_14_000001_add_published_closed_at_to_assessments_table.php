<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPublishedClosedAtToAssessmentsTable extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('assessments')) return;

        Schema::table('assessments', function (Blueprint $table) {
            if (!Schema::hasColumn('assessments', 'published_at')) {
                $table->timestamp('published_at')->nullable()->after('status');
            }
            if (!Schema::hasColumn('assessments', 'closed_at')) {
                $table->timestamp('closed_at')->nullable()->after('published_at');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('assessments')) return;

        Schema::table('assessments', function (Blueprint $table) {
            if (Schema::hasColumn('assessments', 'closed_at')) $table->dropColumn('closed_at');
            if (Schema::hasColumn('assessments', 'published_at')) $table->dropColumn('published_at');
        });
    }
}
