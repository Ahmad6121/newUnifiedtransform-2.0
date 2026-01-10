<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MySalaryController extends Controller
{
    private function isAdmin($u): bool
    {
        if (!$u) return false;

        $role = strtolower((string)($u->role ?? ''));
        $is = ($role === 'admin');

        if (method_exists($u, 'hasRole')) {
            $is = $is || $u->hasRole('admin');
        }

        return $is;
    }

    private function isTeacher($u): bool
    {
        if (!$u) return false;

        $role = strtolower((string)($u->role ?? ''));
        $is = ($role === 'teacher');

        if (method_exists($u, 'hasRole')) {
            $is = $is || $u->hasRole('teacher');
        }

        return $is;
    }

    private function normalizeRowsFromSalaryPayments($rows): Collection
    {
        return collect($rows)->map(function ($r) {
            $salaryMonth = $r->salary_month ? Carbon::parse($r->salary_month) : null;

            $r->title = 'Salary - ' . ($salaryMonth ? $salaryMonth->format('M Y') : '');
            $r->date_label = $salaryMonth ? $salaryMonth->format('Y-m') : '-';
            $r->paid_at_label = $r->paid_at ? Carbon::parse($r->paid_at)->format('Y-m-d H:i') : '-';
            $r->amount = (float)($r->amount ?? 0);

            $r->source_table = 'salary_payments';
            return $r;
        });
    }

    private function normalizeRowsFromPayrolls($rows): Collection
    {
        return collect($rows)->map(function ($r) {
            $payrollDate = $r->payroll_date ? Carbon::parse($r->payroll_date) : null;

            $r->title = $r->title ?? 'Salary';
            $r->date_label = $payrollDate ? $payrollDate->format('Y-m-d') : '-';
            $r->paid_at_label = $r->created_at ? Carbon::parse($r->created_at)->format('Y-m-d H:i') : '-';
            $r->amount = (float)($r->amount ?? 0);

            $r->source_table = 'payrolls';
            return $r;
        });
    }

    public function index(Request $request)
    {
        $u = $request->user();
        if (!$u) abort(403);

        // ✅ فقط Teacher (و Admin اختياري)
        if (!$this->isTeacher($u) && !$this->isAdmin($u)) {
            abort(403);
        }

        // ✅ فلتر شهر (input type=date ممكن يجي بأي يوم، فبنحوّله لبداية الشهر)
        $month = $request->query('month');
        $monthDate = $month ? Carbon::parse($month)->startOfMonth() : now()->startOfMonth();
        $start = $monthDate->copy()->startOfMonth();
        $end   = $monthDate->copy()->endOfMonth();

        // ✅ base salary من users.base_salary
        $baseSalary = 0.0;
        if (Schema::hasColumn('users', 'base_salary')) {
            $baseSalary = (float)($u->base_salary ?? 0);
        }

        $rows = collect();

        // ==========================
        // 1) جرّب salary_payments لو فيه بيانات فعلاً
        // ==========================
        if (Schema::hasTable('salary_payments')) {
            $exists = DB::table('salary_payments')
                ->where('user_id', $u->id)
                ->whereBetween('salary_month', [$start->toDateString(), $end->toDateString()])
                ->exists();

            if ($exists) {
                $raw = DB::table('salary_payments')
                    ->where('user_id', $u->id)
                    ->whereBetween('salary_month', [$start->toDateString(), $end->toDateString()])
                    ->orderByDesc('salary_month')
                    ->orderByDesc('id')
                    ->get();

                $rows = $this->normalizeRowsFromSalaryPayments($raw);
            }
        }

        // ==========================
        // 2) fallback على payrolls (وهذا اللي شغال عندك حاليًا)
        // ==========================
        if ($rows->isEmpty() && Schema::hasTable('payrolls')) {
            $ref = 'user|' . $u->id;

            $raw = DB::table('payrolls')
                ->where('employee_ref', $ref)
                ->whereBetween('payroll_date', [$start->toDateString(), $end->toDateString()])
                ->orderByDesc('payroll_date')
                ->orderByDesc('id')
                ->get();

            $rows = $this->normalizeRowsFromPayrolls($raw);
        }

        $paidThisMonth = (float)$rows->sum('amount');

        return view('finance.my_salary', [
            'month' => $start->format('Y-m-01'),
            'baseSalary' => $baseSalary,
            'paidThisMonth' => $paidThisMonth,
            'rows' => $rows,
        ]);
    }
}
