<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Expense;
use App\Models\Payroll;
use App\Models\User;
use App\Models\Staff;
use Carbon\Carbon;

class FinanceDashboardController extends Controller
{
    private function ensureFinanceAccess()
    {
        $u = auth()->user();
        if (!$u) abort(403);

        $role = strtolower((string)($u->role ?? ''));
        $isAdmin = ($role === 'admin');
        $isAccountant = ($role === 'accountant');

        if (method_exists($u, 'hasRole')) {
            $isAdmin = $isAdmin || $u->hasRole('admin');
            $isAccountant = $isAccountant || $u->hasRole('accountant');
        }

        if (!$isAdmin && !$isAccountant) abort(403);
    }

    public function index(\Illuminate\Http\Request $request)
    {
        $this->ensureFinanceAccess();

        // ✅ فلترة from/to (افتراضي آخر 30 يوم)
        $from = $request->get('from');
        $to   = $request->get('to');

        if (!$from) $from = now()->subDays(30)->format('Y-m-d');
        if (!$to)   $to   = now()->format('Y-m-d');

        $fromDate = Carbon::parse($from)->startOfDay();
        $toDate   = Carbon::parse($to)->endOfDay();

        // ✅ Income = مجموع المدفوع من الفواتير (حسب due_date لو موجودة، وإلا created_at)
        $incomeQuery = Invoice::query();
        if (\Schema::hasColumn('invoices', 'due_date')) {
            $incomeQuery->whereBetween('due_date', [$fromDate->toDateString(), $toDate->toDateString()]);
        } else {
            $incomeQuery->whereBetween('created_at', [$fromDate, $toDate]);
        }
        $income = (float) $incomeQuery->sum('paid_amount');

        // ✅ Expenses
        $expenses = 0.0;
        $recentExpenses = collect();
        if (class_exists(Expense::class)) {
            $expenseQuery = Expense::query();
            if (\Schema::hasColumn('expenses', 'expense_date')) {
                $expenseQuery->whereBetween('expense_date', [$fromDate->toDateString(), $toDate->toDateString()]);
            } else {
                $expenseQuery->whereBetween('created_at', [$fromDate, $toDate]);
            }

            $expenses = (float) $expenseQuery->sum('amount');

            $recentExpenses = Expense::orderByDesc(\Schema::hasColumn('expenses', 'expense_date') ? 'expense_date' : 'id')
                ->limit(10)
                ->get();
        }

        // ✅ Salaries = مجموع الرواتب المدفوعة داخل from/to حسب payroll_date
        $salaryQuery = Payroll::query();
        if (\Schema::hasColumn('payrolls', 'payroll_date')) {
            $salaryQuery->whereBetween('payroll_date', [$fromDate->toDateString(), $toDate->toDateString()]);
        } else {
            $salaryQuery->whereBetween('created_at', [$fromDate, $toDate]);
        }
        $salaries = (float) $salaryQuery->sum('amount');

        $net = $income - ($expenses + $salaries);

        // ✅ Recent Salaries (آخر 10) + تجهيز employee_name & month_label
        $recentSalaries = Payroll::orderByDesc(\Schema::hasColumn('payrolls', 'payroll_date') ? 'payroll_date' : 'id')
            ->limit(10)
            ->get();

        $userIds = [];
        $staffIds = [];

        foreach ($recentSalaries as $p) {
            $ref = (string)($p->employee_ref ?? '');
            if (str_starts_with($ref, 'user|'))  $userIds[] = (int)explode('|', $ref)[1];
            if (str_starts_with($ref, 'staff|')) $staffIds[] = (int)explode('|', $ref)[1];
        }

        $users = !empty($userIds)
            ? User::whereIn('id', array_unique($userIds))->get()->keyBy('id')
            : collect();

        $staff = !empty($staffIds)
            ? Staff::whereIn('id', array_unique($staffIds))->get()->keyBy('id')
            : collect();

        $recentSalaries = $recentSalaries->map(function ($p) use ($users, $staff) {
            $ref = (string)($p->employee_ref ?? '');
            $employeeName = '-';

            if (str_starts_with($ref, 'user|')) {
                $id = (int)explode('|', $ref)[1];
                $u = $users[$id] ?? null;
                if ($u) {
                    $employeeName = trim(($u->first_name ?? '').' '.($u->last_name ?? ''))
                        ?: ($u->name ?? $u->email ?? ('User #'.$id));
                }
            }

            if (str_starts_with($ref, 'staff|')) {
                $id = (int)explode('|', $ref)[1];
                $s = $staff[$id] ?? null;
                if ($s) {
                    $employeeName = trim(($s->first_name ?? '').' '.($s->last_name ?? ''))
                        ?: ('Staff #'.$id);
                }
            }

            $p->employee_name = $employeeName;

            $dateField = $p->payroll_date ?? $p->created_at ?? null;
            $p->month_label = $dateField ? Carbon::parse($dateField)->format('Y-m') : '-';

            return $p;
        });

        return view('finance.dashboard.index', compact(
            'from', 'to',
            'income', 'expenses', 'salaries', 'net',
            'recentExpenses', 'recentSalaries'
        ));
    }
}
