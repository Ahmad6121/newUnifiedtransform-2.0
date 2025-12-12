<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\JobTitle;
use App\Models\SchoolSession;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class StaffController extends Controller
{
    public function index()
    {
        $staff = Staff::with(['session', 'jobTitle'])->paginate(20);
        return view('staff.index', compact('staff'));
    }

    public function create()
    {
        $session   = SchoolSession::latest()->first();
        $jobTitles = JobTitle::all();
        $roles     = Role::pluck('name', 'name'); // جلب أسماء الأدوار من Spatie

        return view('staff.create', compact('session', 'jobTitles', 'roles'));
    }

    public function store(Request $request)
    {
        // ✅ تحقق من البيانات
        $data = $request->validate([
            'first_name'   => 'required|string|max:80',
            'last_name'    => 'required|string|max:80',
            'email'        => 'nullable|email|unique:users,email',
            'phone'        => 'nullable|string|max:20',
            'job_title_id' => 'required|exists:job_titles,id',
            'salary_type'  => 'required|in:fixed,hourly',
            'base_salary'  => 'required|numeric|min:0',
            'join_date'    => 'nullable|date',
            'status'       => 'required|in:active,inactive',
            'role'         => 'nullable|exists:roles,name',
        ]);

        $data['session_id'] = SchoolSession::latest()->value('id');

        // 🧑‍💼 إنشاء سجل Staff
        $staff = Staff::create($data);

        // 🧑‍💻 إنشاء حساب مستخدم إذا كان هناك Role محدد
        // إنشاء حساب مستخدم إذا تم اختيار Role
        if ($request->filled('role') && $request->filled('email')) {
            $user = User::create([
                'first_name'  => $data['first_name'],
                'last_name'   => $data['last_name'],
                'email'       => $data['email'],
                'phone'       => $data['phone'] ?? null,
                'role'        => $request->role,
                'password'    => Hash::make('password123'),
                'gender'      => 'male',           // ✅ قيمة افتراضية
                'nationality' => 'Jordanian',      // ✅ قيمة افتراضية
                'address'     => 'Main Street',
                'address2'    => 'N/A',
                'city'        => 'Amman',
                'zip'         => '11118',
            ]);

            $user->assignRole($request->role);
            $staff->update(['user_id' => $user->id]);
        }


        return redirect()
            ->route('staff.index')
            ->with('status', '✅ Staff member added successfully');
    }

    public function edit(Staff $staff)
    {
        $jobTitles = JobTitle::all();
        $roles = Role::pluck('name', 'name');
        return view('staff.edit', compact('staff', 'jobTitles', 'roles'));
    }

    public function update(Request $request, Staff $staff)
    {
        $data = $request->validate([
            'job_title_id' => 'required|exists:job_titles,id',
            'salary_type'  => 'required|in:fixed,hourly',
            'base_salary'  => 'required|numeric|min:0',
            'join_date'    => 'nullable|date',
            'status'       => 'required|in:active,inactive',
        ]);

        $staff->update($data);

        return redirect()
            ->route('staff.index')
            ->with('status', '✅ Staff updated successfully');
    }

    public function destroy(Staff $staff)
    {
        $staff->delete();
        return back()->with('status', '🗑 Staff deleted');
    }
}
