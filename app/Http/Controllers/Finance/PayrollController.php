<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Payroll;
use App\Models\User;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use App\Models\SchoolSession;


class PayrollController extends Controller
{




    private function detectStaffModel()
    {
        // جرّب موديلات شائعة
        if (class_exists(\App\Models\StaffEmployee::class)) return \App\Models\StaffEmployee::class;
        if (class_exists(\App\Models\Employee::class)) return \App\Models\Employee::class;
        if (class_exists(\App\Models\Staff::class)) return \App\Models\Staff::class;

        return null;
    }

    private function buildEmployeesList()
    {
        $employees = [];

        // ✅ Users: Teachers + Accountants + Staff(لو موجود كـ role)
        $q = User::query();

        // لو عندك role column
        if (Schema::hasColumn('users', 'role')) {
            $q->whereIn('role', ['teacher', 'accountant', 'staff']);
        } else {
            // لو بس spatie roles
            if (method_exists(User::class, 'role')) {
                // لا شيء
            }
        }

        $users = $q->get();

        // لو انت تعتمد Spatie فقط، قد يكون الاستعلام أعلاه ما جاب شيء
        // فنعمل fallback ذكي: نجيب كل المستخدمين ثم نفلتر بالـ hasRole
        if ($users->isEmpty() && method_exists(auth()->user(), 'hasRole')) {
            $users = User::all()->filter(function ($u) {
                return (method_exists($u, 'hasRole') && ($u->hasRole('teacher') || $u->hasRole('accountant') || $u->hasRole('staff')));
            })->values();
        }

        foreach ($users as $u) {
            $name = trim(($u->first_name ?? '') . ' ' . ($u->last_name ?? ''));
            if ($name === '') $name = $u->name ?? $u->email ?? ('User #' . $u->id);

            $roleLabel = $u->role ?? '';
            if ($roleLabel === '' && method_exists($u, 'getRoleNames')) {
                $roleLabel = implode(',', $u->getRoleNames()->toArray());
            }

            $employees[] = [
                'type'  => User::class,
                'id'    => $u->id,
                'name'  => $name,
                'group' => ($u->role === 'accountant' ? 'Accountants' : ($u->role === 'staff' ? 'Staff (Users)' : 'Teachers')),
                'meta'  => $roleLabel,
            ];
        }

        // ✅ Staff from separate table (لو موجود)
        $staffModel = $this->detectStaffModel();
        if ($staffModel) {
            $staffRows = $staffModel::query()->get();
            foreach ($staffRows as $s) {
                $sName = trim(($s->first_name ?? '') . ' ' . ($s->last_name ?? ''));
                if ($sName === '') $sName = $s->name ?? $s->full_name ?? ('Staff #' . $s->id);

                $employees[] = [
                    'type'  => $staffModel,
                    'id'    => $s->id,
                    'name'  => $sName,
                    'group' => 'Staff',
                    'meta'  => 'staff',
                ];
            }
        }

        // group sorting
        usort($employees, function ($a, $b) {
            return strcmp($a['group'] . $a['name'], $b['group'] . $b['name']);
        });

        return $employees;
    }




    public function create()
    {
        $this->ensureFinanceAccess();

        $payroll = new Payroll();
        $employees = $this->buildEmployeesList();

        return view('finance.payroll.create', compact('payroll', 'employees'));
    }

    public function store(Request $request)
    {
        $this->ensureFinanceAccess();

        $data = $request->validate([
            'employee_ref'  => 'required|string',
            'title'         => 'required|string|max:255',
            'amount'        => 'required|numeric|min:0',
            'payroll_date'  => 'required|date',
            'notes'         => 'nullable|string',
        ]);

        // employee_ref format: "ClassName|ID"
        $parts = explode('|', $data['employee_ref']);
        $empType = isset($parts[0]) ? $parts[0] : '';
        $empId   = isset($parts[1]) ? (int)$parts[1] : 0;

        if (!$empType || !$empId) {
            return back()->withErrors(['employee_ref' => 'Invalid employee selection'])->withInput();
        }

        $p = new Payroll();
        $p->employee_type = $empType;
        $p->employee_id   = $empId;
        $p->employee_ref  = $data['employee_ref'];

        $p->title        = $data['title'];
        $p->amount       = $data['amount'];
        $p->payroll_date = $data['payroll_date'];
        $p->notes        = $data['notes'] ?? null;

        if (Schema::hasColumn('payrolls', 'created_by')) {
            $p->created_by = auth()->id();
        }

        $p->save();

        return redirect()->route('finance.payroll.index')->with('status', 'Payroll created ✅');
    }

    public function edit(Payroll $payroll)
    {
        $this->ensureFinanceAccess();

        $employees = $this->buildEmployeesList();
        return view('finance.payroll.edit', compact('payroll', 'employees'));
    }

    public function update(Request $request, Payroll $payroll)
    {
        $this->ensureFinanceAccess();

        $data = $request->validate([
            'employee_ref'  => 'required|string',
            'title'         => 'required|string|max:255',
            'amount'        => 'required|numeric|min:0',
            'payroll_date'  => 'required|date',
            'notes'         => 'nullable|string',
        ]);

        $parts = explode('|', $data['employee_ref']);
        $empType = isset($parts[0]) ? $parts[0] : '';
        $empId   = isset($parts[1]) ? (int)$parts[1] : 0;

        if (!$empType || !$empId) {
            return back()->withErrors(['employee_ref' => 'Invalid employee selection'])->withInput();
        }

        $payroll->employee_type = $empType;
        $payroll->employee_id   = $empId;
        $payroll->employee_ref  = $data['employee_ref'];

        $payroll->title        = $data['title'];
        $payroll->amount       = $data['amount'];
        $payroll->payroll_date = $data['payroll_date'];
        $payroll->notes        = $data['notes'] ?? null;

        $payroll->save();

        return redirect()->route('finance.payroll.index')->with('status', 'Payroll updated ✅');
    }

    public function destroy(Payroll $payroll)
    {
        $this->ensureFinanceAccess();

        $payroll->delete();
        return back()->with('status', 'Payroll deleted ✅');
    }
    public function setSalary(Request $request)
    {
        $this->ensureFinanceAccess();

        $data = $request->validate([
            'employee_ref' => 'required|string',
            'base_salary'  => 'required|numeric|min:0',
        ]);

        [$type, $id] = $this->parseEmployeeRef($data['employee_ref']);
        if (!$type || !$id) {
            return back()->withErrors(['employee_ref' => 'Invalid employee ref'])->withInput();
        }

        $salary = (float)$data['base_salary'];

        if ($type === 'staff') {
            Staff::where('id', $id)->update(['base_salary' => $salary]);
            return back()->with('status', 'Base salary saved ✅');
        }

        // type === user
        if (!Schema::hasColumn('users', 'base_salary')) {
            return back()->withErrors([
                'base_salary' => 'users.base_salary column not found. Add migration to store teacher/accountant salary.'
            ])->withInput();
        }

        User::where('id', $id)->update(['base_salary' => $salary]);
        return back()->with('status', 'Base salary saved ✅');
    }

    // ✅ دفع راتب (ينشئ Payroll record)
    public function pay(Request $request)
    {
        $this->ensureFinanceAccess();

        $data = $request->validate([
            'employee_ref' => 'required|string',
            'salary_month' => 'required|date',
            'amount'       => 'required|numeric|min:0.01',
        ]);

        [$type, $id] = $this->parseEmployeeRef($data['employee_ref']);
        if (!$type || !$id) {
            return back()->withErrors(['employee_ref' => 'Invalid employee ref'])->withInput();
        }

        $salaryMonth = Carbon::parse($data['salary_month'])->startOfMonth();
        $title = 'Salary - ' . $salaryMonth->format('M Y');

        $p = new Payroll();
        $p->employee_type = $type;          // user / staff
        $p->employee_id   = $id;
        $p->employee_ref  = $data['employee_ref'];

        $p->title        = $title;
        $p->amount       = (float)$data['amount'];
        $p->payroll_date = $salaryMonth->toDateString();
        $p->notes        = 'Monthly salary payment';

        if (Schema::hasColumn('payrolls', 'created_by')) {
            $p->created_by = auth()->id();
        }

        $p->save();

        return back()->with('status', 'Salary paid ✅');
    }



    private function currentSessionId(): int
    {
        if (session()->has('browse_session_id')) {
            return (int) session('browse_session_id');
        }

        $latest = SchoolSession::latest()->first();
        return $latest ? (int) $latest->id : 1;
    }

    private function ensureFinanceAccess()
    {
        $u = auth()->user();
        if (!$u) abort(403);

        $roleCol = strtolower((string)($u->role ?? ''));

        $isAdmin = ($roleCol === 'admin');
        $isAccountant = ($roleCol === 'accountant');

        if (method_exists($u, 'hasRole')) {
            $isAdmin = $isAdmin || $u->hasRole('admin');
            $isAccountant = $isAccountant || $u->hasRole('accountant');
        }

        if (!$isAdmin && !$isAccountant) abort(403);
    }

    private function parseEmployeeRef(string $ref): array
    {
        // expected: user|12 or staff|5
        $parts = explode('|', $ref, 2);
        $type = strtolower(trim($parts[0] ?? ''));
        $id   = (int)($parts[1] ?? 0);

        if (!in_array($type, ['user', 'staff'], true) || $id <= 0) {
            return ['', 0];
        }

        return [$type, $id];
    }

    public function index(Request $request)
    {
        $this->ensureFinanceAccess();

        $month = $request->get('month');
        if (!$month) $month = now()->format('Y-m-01');

        $start = Carbon::parse($month)->startOfMonth();
        $end   = Carbon::parse($month)->endOfMonth();

        // ✅ 1) Users (Teachers + Accountants + Staff users لو عندك)
        $usersQ = User::query();

        if (Schema::hasColumn('users', 'role')) {
            $usersQ->whereIn('role', ['teacher', 'accountant', 'staff']);
        } else {
            // fallback: لو بس spatie
            $usersQ->whereRaw('1=0');
        }

        $users = $usersQ->get();

        // fallback لو spatie فقط
        if ($users->isEmpty() && auth()->user() && method_exists(auth()->user(), 'hasRole')) {
            $users = User::all()->filter(function ($u) {
                return method_exists($u, 'hasRole') && (
                        $u->hasRole('teacher') || $u->hasRole('accountant') || $u->hasRole('staff')
                    );
            })->values();
        }

        // ✅ 2) Staff table (Driver/Cleaner ..)
        $staffRows = Staff::with('jobTitle')
            ->where('session_id', $this->currentSessionId())
            ->get();

        // ✅ 3) Payroll payments داخل الشهر
        $paymentsByRef = Payroll::query()
            ->whereBetween('payroll_date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->groupBy('employee_ref');

        $employees = collect();

        foreach ($users as $u) {
            $ref  = 'user|' . $u->id;
            $name = trim(($u->first_name ?? '').' '.($u->last_name ?? '')) ?: ($u->name ?? ('User #'.$u->id));
            $paid = isset($paymentsByRef[$ref]) ? $paymentsByRef[$ref]->sum('amount') : 0;

            $baseSalary = 0;
            if (Schema::hasColumn('users', 'base_salary')) {
                $baseSalary = (float)($u->base_salary ?? 0);
            }

            $employees->push([
                'ref' => $ref,
                'name' => $name,
                'role' => $u->role ?? 'user',
                'base_salary' => $baseSalary,
                'paid' => (float)$paid,
            ]);
        }

        foreach ($staffRows as $s) {
            $ref  = 'staff|' . $s->id;
            $name = trim(($s->first_name ?? '').' '.($s->last_name ?? '')) ?: ('Staff #'.$s->id);
            $role = optional($s->jobTitle)->name ?: 'staff';
            $paid = isset($paymentsByRef[$ref]) ? $paymentsByRef[$ref]->sum('amount') : 0;

            $employees->push([
                'ref' => $ref,
                'name' => $name,
                'role' => $role,
                'base_salary' => (float)($s->base_salary ?? 0),
                'paid' => (float)$paid,
            ]);
        }

        $employees = $employees->sortBy('name')->values();

        return view('finance.payroll.index', compact('employees', 'month'));
    }


}
