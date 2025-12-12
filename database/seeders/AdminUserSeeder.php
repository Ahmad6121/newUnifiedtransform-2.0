<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // إنشاء المستخدم الأدمن إذا مش موجود
        $user = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'first_name' => 'Admin',
                'last_name' => 'User',
                'gender' => 'male',
                'nationality' => 'Jordanian',
                'phone' => '0000000000',
                'address' => 'Main Street',
                'address2' => 'N/A',
                'city' => 'Amman',
                'zip' => '11118',
                'role' => 'admin',
                'password' => Hash::make('password'),
            ]
        );

        // إنشاء الدور Admin إذا مش موجود
        $role = Role::firstOrCreate(['name' => 'Super Admin']);

        // ربط كل الصلاحيات بالدور
        $role->syncPermissions(Permission::all());

        // ربط المستخدم بالدور إذا مش مربوط
        if (!$user->hasRole($role->name)) {
            $user->assignRole($role);
        }
    }
}

