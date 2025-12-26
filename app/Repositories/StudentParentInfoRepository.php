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
            $parentUserId = null;

            // إنشاء حساب ولي الأمر فقط إذا تم إدخال إيميل وباسورد
            if (!empty($request['parent_email']) && !empty($request['parent_password'])) {

                $parentUser = User::create([
                    'first_name'   => $request['father_name'] ?? 'Parent',
                    'last_name'    => ' ', // NOT NULL (خليه مسافة لتفادي الفشل)
                    'email'        => $request['parent_email'],

                    'phone'        => $request['father_phone'] ?? '-',
                    'address'      => $request['parent_address'] ?? '-',

                    // ✅ هذا أهم سطر لحل مشكلتك الحالية
                    'address2'     => '-', // NOT NULL في جدول users

                    // ✅ خليهم NULL/افتراضي بدون ما نضيف حقول بالفورم
                    'gender'       => null,
                    'nationality'  => null,
                    'city'         => null,
                    'zip'          => null,
                    'birthday'     => null,
                    'religion'     => null,
                    'blood_type'   => null,
                    'photo'        => null,

                    'role'         => 'parent',
                    'password'     => Hash::make($request['parent_password']),
                ]);

                // Spatie role
                if (method_exists($parentUser, 'assignRole')) {
                    $parentUser->assignRole('parent');
                }

                $parentUserId = $parentUser->id;
            }

            StudentParentInfo::create([
                'student_id'     => $student_id,
                'father_name'    => $request['father_name'],
                'father_phone'   => $request['father_phone'],
                'mother_name'    => $request['mother_name'],
                'mother_phone'   => $request['mother_phone'],
                'parent_address' => $request['parent_address'],
                'parent_user_id' => $parentUserId,
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
            ]);
        } catch (\Exception $e) {
            throw new \Exception('Failed to update Student Parent information. ' . $e->getMessage());
        }
    }
}
