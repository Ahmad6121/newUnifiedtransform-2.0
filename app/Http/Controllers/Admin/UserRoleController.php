<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;

class UserRoleController extends Controller
{
    /**
     * عرض جميع المستخدمين مع أدوارهم
     */
    public function index()
    {
        $users = User::with('roles')->paginate(15);
        return view('admin.index', compact('users'));
    }

    /**
     * تعديل أدوار مستخدم معين
     */
    public function edit(User $user)
    {
        $roles = Role::all();
        return view('admin.edit', compact('user', 'roles'));
    }

    /**
     * حفظ الأدوار بعد التعديل
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'roles' => 'array',
        ]);

        $user->syncRoles($request->roles ?? []);

        return redirect()->route('admin.users.index')
            ->with('success', 'تم تحديث صلاحيات المستخدم بنجاح');
    }
    public function search(Request $request)
    {
        $term = $request->get('q');

        $users = \App\Models\User::with('roles')
            ->where('first_name', 'like', "%{$term}%")
            ->orWhere('last_name', 'like', "%{$term}%")
            ->orWhere('email', 'like', "%{$term}%")
            ->get();

        return response()->json($users);
    }

}

