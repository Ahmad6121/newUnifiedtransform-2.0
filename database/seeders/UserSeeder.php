<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Course;
use App\Models\AssignedTeacher;
use App\Models\Promotion;
use App\Models\SchoolSession;
use App\Models\Semester;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
public function run(): void
{
$session  = SchoolSession::latest()->first();
$semester = Semester::latest()->first();

if (!$session || !$semester) {
$this->command->warn('⚠️ لا يوجد Session أو Semester — تأكد من تشغيل SchoolSessionSeeder و SemesterSeeder أولاً.');
return;
}

$classes  = SchoolClass::all();
$sections = Section::with('schoolClass')->get();
$courses  = Course::all();

if ($classes->isEmpty() || $sections->isEmpty() || $courses->isEmpty()) {
$this->command->warn('⚠️ لا توجد صفوف أو شعب أو مواد — تأكد من تشغيل SchoolClassSeeder, SectionSeeder, CourseSeeder أولاً.');
return;
}

$studentNames = ['Ahmad', 'Mohammad', 'Ali', 'Omar', 'Amir'];
$subjects = ['Mathematics', 'Science', 'English', 'Arabic', 'History'];
$commonTeachers = [];

// 🧑‍🏫 1️⃣ إنشاء معلمين موحدين للصفوف 1-6
foreach ($subjects as $subject) {
$teacher = User::firstOrCreate(
['email' => strtolower($subject) . "_teacher@gmail.com"],
[
'first_name'  => 'Teacher',
'last_name'   => $subject,
'gender'      => 'male',
'nationality' => 'Jordanian',
'phone'       => '079' . rand(1000000, 9999999),
'role'        => 'teacher',
'password'    => Hash::make('password'),
'address'     => 'Main Street',
'address2'    => 'N/A',
'city'        => 'Amman',
'zip'         => '11118',
]
);

// 🆕 ربط المعلم مع Role "teacher" في Spatie
if (!$teacher->hasRole('teacher')) {
$teacher->assignRole('teacher');
}

$commonTeachers[$subject] = $teacher;
}

foreach ($courses as $course) {
if ($course->class_id <= 6 && isset($commonTeachers[$course->course_name])) {
$teacherId = $commonTeachers[$course->course_name]->id;
foreach ($sections->where('class_id', $course->class_id) as $section) {
AssignedTeacher::firstOrCreate([
'teacher_id'  => $teacherId,
'course_id'   => $course->id,
'class_id'    => $course->class_id,
'section_id'  => $section->id,
'session_id'  => $session->id,
'semester_id' => $semester->id,
]);
}
}
}

// 🧑‍🏫 2️⃣ إنشاء معلمين منفصلين للصفوف 7-12
foreach (range(7, 12) as $grade) {
foreach ($subjects as $subject) {
$teacher = User::firstOrCreate(
['email' => strtolower($subject) . "_teacher_grade{$grade}@gmail.com"],
[
'first_name'  => 'Teacher',
'last_name'   => "{$subject}_G{$grade}",
'gender'      => 'male',
'nationality' => 'Jordanian',
'phone'       => '079' . rand(1000000, 9999999),
'role'        => 'teacher',
'password'    => Hash::make('password'),
'address'     => 'Main Street',
'address2'    => 'N/A',
'city'        => 'Amman',
'zip'         => '11118',
]
);

if (!$teacher->hasRole('teacher')) {
$teacher->assignRole('teacher');
}

foreach ($courses->where('class_id', $grade)->where('course_name', $subject) as $course) {
foreach ($sections->where('class_id', $grade) as $section) {
AssignedTeacher::firstOrCreate([
'teacher_id'  => $teacher->id,
'course_id'   => $course->id,
'class_id'    => $course->class_id,
'section_id'  => $section->id,
'session_id'  => $session->id,
'semester_id' => $semester->id,
]);
}
}
}
}

// 👨‍🎓 3️⃣ إنشاء الطلاب لكل شعبة
foreach ($sections as $section) {
foreach ($studentNames as $index => $name) {
$student = User::firstOrCreate(
['email' => strtolower($name) . "_grade" . ($section->schoolClass->class_name ?? 'X') . "_sec{$section->id}_id{$index}@gmail.com"],
[
'first_name'  => $name,
'last_name'   => "Student",
'gender'      => 'male',
'nationality' => 'Jordanian',
'phone'       => '078' . rand(1000000, 9999999),
'role'        => 'student',
'password'    => Hash::make('password'),
'address'     => 'Main Street',
'address2'    => 'N/A',
'city'        => 'Amman',
'zip'         => '11118',
]
);

if (!$student->hasRole('student')) {
$student->assignRole('student');
}

Promotion::firstOrCreate([
'student_id' => $student->id,
'class_id'   => $section->class_id,
'section_id' => $section->id,
'session_id' => $session->id,
'id_card_number' => "ID-{$student->id}",
]);
}
}

$this->command->info('✅ تم إنشاء الطلاب والمعلمين وربطهم بالأدوار والصلاحيات بنجاح.');
}
}

