<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AssignedTeacher;
use App\Models\Course;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\User;
use App\Models\SchoolSession;

class AssignedTeacherSeeder extends Seeder
{
    public function run(): void
    {
        $session = SchoolSession::latest()->first();

        if (!$session) {
            $this->command->warn("⚠️ لا يوجد Session. شغّل SchoolSessionSeeder أولاً.");
            return;
        }

        // جلب المعلمين حسب أسمائهم
        $mathTeacher    = User::where('first_name', 'Teacher Mathematics')->first();
        $scienceTeacher = User::where('first_name', 'Teacher Science')->first();
        $englishTeacher = User::where('first_name', 'Teacher English')->first();
        $arabicTeacher  = User::where('first_name', 'Teacher Arabic')->first();
        $historyTeacher = User::where('first_name', 'Teacher History')->first();

        $teacherMap = [
            'Mathematics' => $mathTeacher ? $mathTeacher->id : null,
            'Science'     => $scienceTeacher ? $scienceTeacher->id : null,
            'English'     => $englishTeacher ? $englishTeacher->id : null,
            'Arabic'      => $arabicTeacher ? $arabicTeacher->id : null,
            'History'     => $historyTeacher ? $historyTeacher->id : null,
        ];

        $classes = SchoolClass::with('sections')->get();

        foreach ($classes as $class) {
            foreach ($class->sections as $section) {
                $courses = Course::where('class_id', $class->id)
                    ->where('session_id', $session->id)
                    ->get();

                foreach ($courses as $course) {
                    $teacherId = $teacherMap[$course->course_name] ?? null;

                    // ✅ تأكد أن teacherId موجود قبل الحفظ
                    if (!$teacherId) {
                        $this->command->warn("⚠️ لا يوجد معلم معرف للمادة {$course->course_name} - تم تخطيها.");
                        continue;
                    }

                    AssignedTeacher::firstOrCreate(
                        [
                            'teacher_id' => $teacherId,
                            'course_id'  => $course->id,
                            'class_id'   => $class->id,
                            'section_id' => $section->id,
                            'session_id' => $session->id,
                        ]
                    );
                }
            }
        }

        $this->command->info("✅ تم ربط جميع المعلمين بالمواد لجميع الصفوف بنجاح.");
    }
}

