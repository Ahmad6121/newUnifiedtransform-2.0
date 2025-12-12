<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\SchoolSession;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $semester = Semester::latest()->first();
        $session  = SchoolSession::latest()->first();

        if (!$semester) {
            $this->command->warn("⚠️ لا يوجد سمستر في قاعدة البيانات. شغّل SemesterSeeder أولاً.");
            return;
        }

        if (!$session) {
            $this->command->warn("⚠️ لا يوجد Session في قاعدة البيانات. شغّل SchoolSessionSeeder أولاً.");
            return;
        }

        $courses = ['Mathematics', 'Science', 'English', 'Arabic', 'History'];

        $classes = SchoolClass::all();

        foreach ($classes as $class) {
            foreach ($courses as $courseName) {
                Course::firstOrCreate(
                    [
                        'course_name' => $courseName,
                        'class_id'    => $class->id,
                        'semester_id' => $semester->id,
                        'session_id'  => $session->id, // ✅ إضافة session_id
                    ],
                    [
                        'course_type' => 'Core',
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]
                );
            }
        }

        $this->command->info('✅ تم إضافة المواد لكل الصفوف وربطها بالـ semester والـ session بنجاح.');
    }
}
