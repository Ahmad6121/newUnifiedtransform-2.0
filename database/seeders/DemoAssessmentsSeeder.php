<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DemoAssessmentsSeeder extends Seeder
{
    public function run(): void
    {
        if (!Schema::hasTable('assessments')) {
            $this->command->warn('assessments table not found, skipping DemoAssessmentsSeeder.');
            return;
        }

        $session = DB::table('school_sessions')->orderByDesc('id')->first();
        if (!$session) {
            $this->command->warn('No school session found.');
            return;
        }
        $sessionId = (int)$session->id;

        $teacherIds = DB::table('users')->where('role', 'teacher')->pluck('id')->toArray();
        if (empty($teacherIds)) {
            $this->command->warn('No teachers found.');
            return;
        }

        $classes = DB::table('school_classes')->orderBy('id')->get();
        if ($classes->isEmpty()) {
            $this->command->warn('No classes found.');
            return;
        }

        foreach ($classes as $class) {
            $classId = (int)$class->id;

            // section (first one)
            $sectionId = null;
            if (Schema::hasTable('sections') && Schema::hasColumn('sections', 'class_id')) {
                $sec = DB::table('sections')->where('class_id', $classId)->orderBy('id')->first();
                if ($sec) $sectionId = (int)$sec->id;
            } elseif (Schema::hasTable('sections')) {
                $sec = DB::table('sections')->orderBy('id')->first();
                if ($sec) $sectionId = (int)$sec->id;
            }

            // courses for class+session
            $courses = DB::table('courses')
                ->where('session_id', $sessionId)
                ->where('class_id', $classId)
                ->orderBy('id')
                ->get();

            foreach ($courses as $course) {
                $courseId = (int)$course->id;

                // create 2 assessments per course
                for ($i = 1; $i <= 2; $i++) {
                    $teacherId = (int)$teacherIds[array_rand($teacherIds)];

                    $payload = [
                        'title' => ($i === 1 ? 'Quiz' : 'Midterm') . ' - ' . ($course->course_name ?? ('Course #' . $courseId)),
                        'session_id' => $sessionId,
                        'class_id' => $classId,
                        'course_id' => $courseId,
                        'teacher_id' => $teacherId,
                        'total_marks' => ($i === 1 ? 30 : 100),
                        'weight_percent' => ($i === 1 ? 10 : 30),
                        'passing_marks' => ($i === 1 ? 15 : 50),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    // section_id optional
                    if ($sectionId && Schema::hasColumn('assessments', 'section_id')) {
                        $payload['section_id'] = $sectionId;
                    }

                    // status optional
                    if (Schema::hasColumn('assessments', 'status')) {
                        $payload['status'] = 'draft';
                    }

                    DB::table('assessments')->insert($payload);
                }
            }
        }

        $this->command->info('✅ DemoAssessmentsSeeder: created demo assessments for classes/courses.');
    }
}
