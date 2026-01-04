<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Expense;
use App\Models\Payroll;
use App\Models\User;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

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

    private function pickDateColumn(string $table, array $candidates, string $fallback = 'created_at'): string
    {
        foreach ($candidates as $col) {
            if (Schema::hasColumn($table, $col)) return $col;
        }
        return $fallback;
    }

    public function index(Request $request)
    {
        $this->ensureFinanceAccess();

        // فلتر التاريخ
        $from = $request->query('from');
        $to   = $request->query('to');

        $fromDate = $from ? Carbon::parse($from)->startOfDay() : now()->startOfMonth()->startOfDay();
        $toDate   = $to   ? Carbon::parse($to)->endOfDay()     : now()->endOfDay();

        // =======================
        // Income (الأفضل من payments إن وجد)
        // =======================
        $income = 0.0;

        if (Schema::hasTable('payments')) {
            // إذا عندك App\Models\Payment
            $paymentModel = class_exists(\App\Models\Payment::class) ? \App\Models\Payment::class : null;

            if ($paymentModel) {
                $payDateCol = $this->pickDateColumn('payments', ['payment_date', 'paid_at', 'date', 'created_at'], 'created_at');
                $income = (float) $paymentModel::query()
                    ->whereBetween($payDateCol, [$fromDate, $toDate])
                    ->sum('amount');
            }
        }

        // لو ما في payments أو طلع 0، اعمل fallback على invoices
        if ($income <= 0 && Schema::hasTable('invoices')) {
            $invDateCol = $this->pickDateColumn('invoices', ['invoice_date', 'issue_date', 'date', 'created_at'], 'created_at');

            // إذا paid_amount موجود استخدمه، وإلا amount
            $incomeCol = Schema::hasColumn('invoices', 'paid_amount') ? 'paid_amount' : 'amount';

            $income = (float) Invoice::query()
                ->whereBetween($invDateCol, [$fromDate, $toDate])
                ->sum($incomeCol);
        }

        // =======================
        // Expenses
        // =======================
        $expenses = 0.0;
        $recentExpenses = collect();

        if (class_exists(Expense::class) && Schema::hasTable('expenses')) {
            $expDateCol = $this->pickDateColumn('expenses', ['expense_date', 'date', 'created_at'], 'created_at');

            $expenses = (float) Expense::query()
                ->whereBetween($expDateCol, [$fromDate, $toDate])
                ->sum('amount');

            $recentExpenses = Expense::query()
                ->orderByDesc($expDateCol)
                ->orderByDesc('id')
                ->limit(10)
                ->get()
                ->map(function ($e) use ($expDateCol) {
                    $e->date_label = $e->{$expDateCol}
                        ? Carbon::parse($e->{$expDateCol})->format('Y-m-d')
                        : '-';
                    return $e;
                });
        }

        // =======================
        // Salaries (Payroll)
        // =======================
        $payDateCol = Schema::hasTable('payrolls')
            ? $this->pickDateColumn('payrolls', ['payroll_date', 'date', 'created_at'], 'created_at')
            : 'payroll_date';

        $salaries = (float) Payroll::query()
            ->whereBetween($payDateCol, [$fromDate, $toDate])
            ->sum('amount');

        $recentPayrolls = Payroll::query()
            ->orderByDesc($payDateCol)
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        // تجهيز أسماء الموظفين مرة وحدة
        $userIds = [];
        $staffIds = [];

        foreach ($recentPayrolls as $p) {
            $ref = (string)($p->employee_ref ?? '');
            if (str_starts_with($ref, 'user|'))  $userIds[]  = (int)explode('|', $ref)[1];
            if (str_starts_with($ref, 'staff|')) $staffIds[] = (int)explode('|', $ref)[1];
        }

        $users = !empty($userIds)
            ? User::whereIn('id', array_unique($userIds))->get()->keyBy('id')
            : collect();

        $staff = !empty($staffIds)
            ? Staff::whereIn('id', array_unique($staffIds))->get()->keyBy('id')
            : collect();

        $recentPayrolls = $recentPayrolls->map(function ($p) use ($users, $staff, $payDateCol) {
            $ref = (string)($p->employee_ref ?? '');
            $employeeName = '-';

            if (str_starts_with($ref, 'user|')) {
                $id = (int)explode('|', $ref)[1];
                $u = $users[$id] ?? null;
                if ($u) {
                    $employeeName = trim(($u->first_name ?? '') . ' ' . ($u->last_name ?? ''))
                        ?: ($u->name ?? $u->email ?? ('User #' . $id));
                }
            } elseif (str_starts_with($ref, 'staff|')) {
                $id = (int)explode('|', $ref)[1];
                $s = $staff[$id] ?? null;
                if ($s) {
                    $employeeName = trim(($s->first_name ?? '') . ' ' . ($s->last_name ?? ''))
                        ?: ('Staff #' . $id);
                }
            }

            $p->employee_name = $employeeName;
            $p->month_label = $p->{$payDateCol}
                ? Carbon::parse($p->{$payDateCol})->format('M Y')
                : '-';

            return $p;
        });

        // =======================
        // Net
        // =======================
        $net = $income - ($expenses + $salaries);

        return view('finance.dashboard.index', compact(
            'from', 'to',
            'income', 'expenses', 'salaries', 'net',
            'recentExpenses', 'recentPayrolls'
        ));
    }
}
