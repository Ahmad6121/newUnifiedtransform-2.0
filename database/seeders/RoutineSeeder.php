<?php

namespace Database\Seeders;

use App\Models\Routine;
use App\Models\AssignedTeacher;
use App\Models\SchoolClass;
use App\Models\Course;
use App\Models\SchoolSession;
use Illuminate\Database\Seeder;

class RoutineSeeder extends Seeder
{
    public function run(): void
    {
        $session = SchoolSession::latest()->first();
        if (!$session) {
            $this->command->warn("⚠️ لا يوجد جلسة دراسية. شغل SchoolSessionSeeder أولاً.");
            return;
        }

        $days = ['Sunday','Monday','Tuesday','Wednesday','Thursday'];
        $slots = [
            ['08:00','08:50'],
            ['09:00','09:50'],
            ['10:00','10:50'],
            ['11:00','11:50'],
            ['12:00','12:50'],
        ];

        // احذف الجدول القديم لتجنب التكرار
        Routine::where('session_id', $session->id)->delete();

        $classes = SchoolClass::with('sections')->get();

        foreach ($classes as $class) {
            foreach ($class->sections as $section) {
                // 🆕 اسم الغرفة بناءً على الصف والشعبة
                $fixedRoom = "Grade {$class->class_name} - Section {$section->section_name}";

                foreach ($days as $day) {
                    $courses = Course::where('class_id', $class->id)
                        ->where('session_id', $session->id)
                        ->distinct()
                        ->get();

                    $i = 0;
                    foreach ($courses as $course) {
                        $slot = $slots[$i % count($slots)];

                        $teacher = AssignedTeacher::where('course_id', $course->id)
                            ->where('class_id', $class->id)
                            ->where('section_id', $section->id)
                            ->first();

                        if (!Routine::where([
                            'session_id' => $session->id,
                            'class_id'   => $class->id,
                            'section_id' => $section->id,
                            'course_id'  => $course->id,
                            'day'        => $day,
                            'start_time' => $slot[0],
                        ])->exists()) {

                            Routine::create([
                                'session_id' => $session->id,
                                'class_id'   => $class->id,
                                'section_id' => $section->id,
                                'course_id'  => $course->id,
                                'teacher_id' => $teacher ? $teacher->teacher_id : null,
                                'day'        => $day,
                                'start_time' => $slot[0],
                                'end_time'   => $slot[1],
                                'room_no'    => $fixedRoom, // ✅ اسم الغرفة = اسم الصف + الشعبة
                            ]);
                        }

                        $i++;
                    }
                }
            }
        }

        $this->command->info('✅ تم إنشاء جدول الحصص مع تسمية الغرف باسم الصف والشعبة.');
    }
}

