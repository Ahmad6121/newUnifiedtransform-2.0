<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
//            RoleSeeder::class,
            AdminUserSeeder::class,
            SchoolSessionSeeder::class,   // ✅ أنشئ الـ session أولاً
            SemesterSeeder::class,        // ✅ أنشئ الـ semester قبل أي كورسات
            SchoolClassSeeder::class,
            SectionSeeder::class,
            CourseSeeder::class,          // ✅ المواد لازم تنشأ قبل الـ users

            UserSeeder::class,            // ✅ الآن بننشئ الطلاب والمعلمين بعد ما الكورسات جاهزة
            AssignedTeacherSeeder::class, // ✅ بعدها بنربط المعلمين بالمقررات
            RoutineSeeder::class,         // ✅ أخيراً بنعمل جدول الحصص

            // باقي الـ seeders الثانوية
            BookSeeder::class,
            BookIssueSeeder::class,
            InvoiceSeeder::class,
            PaymentSeeder::class,
            JobTitleSeeder::class,
            StaffSeeder::class,
            StudentParentInfoSeeder::class,
            StudentAcademicInfoSeeder::class,
            AcademicSettingSeeder::class,
        ]);
    }
}
