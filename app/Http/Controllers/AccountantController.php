<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

// ✅ Imports ناقصة (مهمّة)
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AccountantController extends Controller
{
    private function ensureAdmin()
    {
        $u = auth()->user();
        if (!$u || $u->role !== 'admin') abort(403);
    }

    // ✅ مسموح للمحاسب يشوفها
    public function index(Request $request)
    {
        $q = User::query()->where('role', 'accountant');

        if ($request->filled('search')) {
            $s = $request->search;
            $q->where(function ($qq) use ($s) {
                $qq->where('first_name', 'like', "%$s%")
                    ->orWhere('last_name', 'like', "%$s%")
                    ->orWhere('email', 'like', "%$s%")
                    ->orWhere('phone', 'like', "%$s%");
            });
        }

        $accountants = $q->latest()->paginate(10);

        return view('accountants.index', compact('accountants'));
    }

    // ❌ Admin only
    public function create()
    {
        $this->ensureAdmin();
        return view('accountants.create');
    }

    public function store(Request $request)
    {
        $this->ensureAdmin();

        $data = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => 'required|email|unique:users,email',
            'phone'      => 'nullable|string|max:50',
            'address'    => 'nullable|string|max:255',
            'password'   => 'required|string|min:6|confirmed',
        ]);

        // ✅ FIX: address لا يكون null أبداً (لأن العمود NOT NULL)
        if (!isset($data['address']) || trim((string)$data['address']) === '') {
            $data['address'] = 'N/A'; // أو '' إذا بتحب فاضي
        }

        $user = new \App\Models\User();
        $user->first_name = $data['first_name'];
        $user->last_name  = $data['last_name'];
        $user->email      = $data['email'];
        $user->phone      = $data['phone'] ?? null;
        $user->address    = $data['address']; // ✅ صار مضمون مش null
        $user->password   = bcrypt($data['password']);

        // ✅ لو عندك role column خليها كمان
        if (Schema::hasColumn('users', 'role')) {
            $user->role = 'accountant';
        }

        $user->save();

        // ✅ تأكد أن Role موجود
        Role::firstOrCreate(['name' => 'accountant', 'guard_name' => 'web']);

        // ✅ أعطِ الدور عبر Spatie
        $user->syncRoles(['accountant']);

        // ✅ امسح كاش الصلاحيات (مهم)
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('accountants.index')->with('status', 'Accountant created successfully ✅');
    }

    public function edit(User $user)
    {
        $this->ensureAdmin();
        abort_unless($user->role === 'accountant', 404);

        // ✅ rename variable for the view
        $accountant = $user;

        return view('accountants.edit', compact('accountant'));
    }

    public function update(Request $request, \App\Models\User $user)
    {
        $this->ensureAdmin();
        abort_unless(
            (Schema::hasColumn('users', 'role') ? $user->role === 'accountant' : true) || (method_exists($user, 'hasRole') && $user->hasRole('accountant')),
            404
        );

        $data = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => 'required|email|unique:users,email,' . $user->id,
            'phone'      => 'nullable|string|max:50',
            'address'    => 'nullable|string|max:255',
            'password'   => 'nullable|string|min:6|confirmed',
        ]);

        // ✅ FIX: address لا يكون null أبداً
        if (!isset($data['address']) || trim((string)$data['address']) === '') {
            $data['address'] = 'N/A';
        }

        $user->first_name = $data['first_name'];
        $user->last_name  = $data['last_name'];
        $user->email      = $data['email'];
        $user->phone      = $data['phone'] ?? null;
        $user->address    = $data['address']; // ✅ صار مضمون مش null

        if (!empty($data['password'])) {
            $user->password = bcrypt($data['password']);
        }

        // ✅ حافظ على role column لو موجود
        if (Schema::hasColumn('users', 'role')) {
            $user->role = 'accountant';
        }

        $user->save();

        // ✅ تأكد Role موجود
        Role::firstOrCreate(['name' => 'accountant', 'guard_name' => 'web']);

        // ✅ ثبّت الدور في Spatie
        $user->syncRoles(['accountant']);

        // ✅ امسح كاش الصلاحيات
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('accountants.index')->with('status', 'Accountant updated successfully ✅');
    }

    public function destroy(User $user)
    {
        $this->ensureAdmin();
        abort_unless($user->role === 'accountant', 404);

        $user->delete();
        return back()->with('success', 'Accountant deleted.');
    }
}
