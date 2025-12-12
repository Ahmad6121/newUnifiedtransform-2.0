<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            // NOTE: keep your existing columns. We only ADD new ones.

            // new scope fields
            if (!Schema::hasColumn('exams', 'section_id')) {
                $table->unsignedInteger('section_id')->nullable()->after('class_id');
            }
            if (!Schema::hasColumn('exams', 'teacher_id')) {
                $table->unsignedInteger('teacher_id')->nullable()->after('course_id');
            }

            // module A fields
            if (!Schema::hasColumn('exams', 'exam_type')) {
                $table->string('exam_type')->default('written')->after('exam_name');
                // written | online
            }
            if (!Schema::hasColumn('exams', 'total_marks')) {
                $table->float('total_marks')->default(100)->after('exam_type');
            }
            if (!Schema::hasColumn('exams', 'passing_marks')) {
                $table->float('passing_marks')->default(50)->after('total_marks');
            }
            if (!Schema::hasColumn('exams', 'duration_minutes')) {
                $table->unsignedInteger('duration_minutes')->nullable()->after('end_date');
            }
            if (!Schema::hasColumn('exams', 'status')) {
                $table->string('status')->default('draft')->after('duration_minutes');
                // draft | published | closed
            }
            if (!Schema::hasColumn('exams', 'is_randomized')) {
                $table->boolean('is_randomized')->default(false)->after('status');
            }
            if (!Schema::hasColumn('exams', 'attempts_allowed')) {
                $table->unsignedInteger('attempts_allowed')->default(1)->after('is_randomized');
            }

            // helpful index (optional)
            $table->index(['session_id', 'semester_id', 'class_id', 'section_id', 'course_id'], 'exams_filter_idx');
        });
    }

    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            // drop index first
            $table->dropIndex('exams_filter_idx');

            $cols = [
                'section_id',
                'teacher_id',
                'exam_type',
                'total_marks',
                'passing_marks',
                'duration_minutes',
                'status',
                'is_randomized',
                'attempts_allowed',
            ];

            foreach ($cols as $col) {
                if (Schema::hasColumn('exams', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
