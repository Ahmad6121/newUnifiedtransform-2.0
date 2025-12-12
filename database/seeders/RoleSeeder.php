<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // 🧑‍💻 إنشاء الأدوار إذا غير موجودة
        $admin      = Role::firstOrCreate(['name' => 'admin']);
        $teacher    = Role::firstOrCreate(['name' => 'teacher']);
        $student    = Role::firstOrCreate(['name' => 'student']);
        $parent     = Role::firstOrCreate(['name' => 'parent']);
        $librarian  = Role::firstOrCreate(['name' => 'librarian']);
        $accountant = Role::firstOrCreate(['name' => 'accountant']);

        // 🔑 ربط الصلاحيات بالأدوار
        $admin->syncPermissions(Permission::all());

        // المعلم يقدر يشوف الطلاب، يأخذ حضور، يعدل علامات مواده فقط
        $teacher->syncPermissions([
            'view students',
            'take attendances',
            'view attendances',
            'create assignments',
            'view assignments',
            'save marks',
            'view marks',
            'view courses',
        ]);

        // الطالب يشوف مواده وواجباته ونتائجه فقط
        $student->syncPermissions([
            'view courses',
            'view assignments',
            'view attendances',
            'view marks',
        ]);

        // ولي الأمر يشوف الطلاب التابعين له ونتائجهم
        $parent->syncPermissions([
            'view students',
            'view marks',
            'view attendances',
        ]);

        // أمين المكتبة
        $librarian->syncPermissions([
            'create books', 'view books', 'edit books', 'delete books',
            'issue books', 'return books', 'view issued books',
        ]);

        // المحاسب
        $accountant->syncPermissions([
            'create invoices', 'view invoices', 'edit invoices', 'delete invoices',
            'record payments', 'view payments',
        ]);

        $this->command->info('✅ RoleSeeder: تم إنشاء الأدوار وربط الصلاحيات بنجاح.');
    }
}
