<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // 1️⃣ إعادة تعيين الكاش
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 2️⃣ إنشاء كل الصلاحيات
        $permissions = [
            // 👥 إدارة المستخدمين
            'create users', 'view users', 'edit users', 'delete users',
            // 👨‍🎓 الطلاب والترقيات
            'view students', 'promote students',
            // 📢 الإعلانات
            'create notices', 'view notices', 'edit notices', 'delete notices',
            // 📅 الفعاليات
            'create events', 'view events', 'edit events', 'delete events',
            // 📚 المناهج
            'create syllabi', 'view syllabi', 'edit syllabi', 'delete syllabi',
            // 🗓️ جداول الحصص
            'create routines', 'view routines', 'edit routines', 'delete routines',
            // 📝 الامتحانات والدرجات
            'create exams', 'view exams', 'delete exams',
            'create exams rule', 'view exams rule', 'edit exams rule', 'delete exams rule',
            'view exams history',
            'create grading systems', 'view grading systems', 'edit grading systems', 'delete grading systems',
            // 🧾 الحضور
            'take attendances', 'view attendances', 'update attendances type',
            // 📄 الواجبات
            'submit assignments', 'create assignments', 'view assignments',
            // ✅ العلامات
            'save marks', 'view marks',
            // 🏫 الإعدادات الأكاديمية
            'create school sessions', 'create semesters', 'view semesters', 'edit semesters', 'assign teachers',
            'create courses', 'view courses', 'edit courses',
            'view academic settings', 'update marks submission window', 'update browse by session',
            'create classes', 'view classes', 'edit classes',
            'create sections', 'view sections', 'edit sections',
            // 📖 المكتبة (صلاحيات مفصلة بدل manage library)
            'create books', 'view books', 'edit books', 'delete books',
            'issue books', 'return books', 'view issued books',
            // 💵 المالية
            'create invoices', 'view invoices', 'edit invoices', 'delete invoices',
            'record payments', 'view payments',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate([
                'name' => $perm,
                'guard_name' => 'web'
            ]);
        }

        // 3️⃣ إنشاء الأدوار وربط الصلاحيات
        $roles = [
            'admin' => Permission::all()->pluck('name')->toArray(),
            'accountant' => ['create invoices', 'view invoices', 'edit invoices', 'delete invoices',
                'record payments', 'view payments', 'view students', 'view users'],
            'librarian' => ['create books', 'view books', 'edit books', 'delete books',
                'issue books', 'return books', 'view issued books', 'view students'],
            'teacher' => [
                'view students',       // يستطيع رؤية طلاب صفوفه
                'take attendances', 'view attendances',
                'create assignments', 'view assignments',
                'create exams', 'view exams',
                'save marks', 'view marks',
                'view classes', 'view courses', 'view routines', 'view syllabi'
            ],


            'student' => ['view marks', 'submit assignments', 'view routines', 'view syllabi'],
            'parent' => ['view marks', 'view routines', 'view notices'],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web'
            ]);
            $role->syncPermissions($rolePermissions);
        }

        $this->command->info('✅ PermissionSeeder: تم إنشاء كل الصلاحيات والأدوار وربطها بنجاح.');
    }
}
