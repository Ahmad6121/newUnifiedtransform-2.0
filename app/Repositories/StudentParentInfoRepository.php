<?php

namespace App\Repositories;

use App\Models\StudentParentInfo;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class StudentParentInfoRepository
{
    public function store($request, $student_id)
    {
        try {
            // 🆕 افتراضيًا ما في parent user
            $parentUserId = null;

            // 🆕 إنشاء حساب لولي الأمر إذا تم إدخال إيميل وباسورد
            if (!empty($request['parent_email']) && !empty($request['parent_password'])) {

                $parentUser = User::create([
                    'first_name' => $request['father_name'] ?? 'Parent',
                    'last_name'  => '', // حسب تصميم جدول users عندك
                    'email'      => $request['parent_email'],
                    'phone'      => $request['father_phone'] ?? null,
                    'address'    => $request['parent_address'] ?? null,
                    'password'   => Hash::make($request['parent_password']),
                ]);

                // إعطاء دور parent (Spatie)
                if (method_exists($parentUser, 'assignRole')) {
                    $parentUser->assignRole('parent');
                }

                $parentUserId = $parentUser->id;
            }

            // إنشاء record معلومات الأهل + ربطه بحساب الأب إن وجد
            StudentParentInfo::create([
                'student_id'     => $student_id,
                'father_name'    => $request['father_name'],
                'father_phone'   => $request['father_phone'],
                'mother_name'    => $request['mother_name'],
                'mother_phone'   => $request['mother_phone'],
                'parent_address' => $request['parent_address'],
                'parent_user_id' => $parentUserId, // 🆕 أهم سطر
            ]);

        } catch (\Exception $e) {
            throw new \Exception('Failed to create Student Parent information. ' . $e->getMessage());
        }
    }

    public function getParentInfo($student_id)
    {
        return StudentParentInfo::where('student_id', $student_id)->first();
    }

    public function update($request, $student_id)
    {
        try {
            StudentParentInfo::where('student_id', $student_id)->update([
                'father_name'    => $request['father_name'],
                'father_phone'   => $request['father_phone'],
                'mother_name'    => $request['mother_name'],
                'mother_phone'   => $request['mother_phone'],
                'parent_address' => $request['parent_address'],
                // لو حاب تضيف تحديث لحساب الأب (email/password) ممكن نضيفه هنا لاحقًا
            ]);
        } catch (\Exception $e) {
            throw new \Exception('Failed to update Student Parent information. ' . $e->getMessage());
        }
    }
}
