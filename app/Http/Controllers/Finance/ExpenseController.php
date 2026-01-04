<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $q = Expense::query();

        if ($request->filled('search')) {
            $s = $request->get('search');
            $q->where(function ($qq) use ($s) {
                $qq->where('title', 'like', "%$s%")
                    ->orWhere('notes', 'like', "%$s%");
            });
        }

        $expenses = $q->latest()->paginate(15);
        return view('finance.expenses.index', compact('expenses'));
    }

    public function create()
    {
        $expense = new Expense();
        return view('finance.expenses.create', compact('expense'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'        => 'required|string|max:255',
            'amount'       => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'notes'        => 'nullable|string',
        ]);

        $expense = new Expense();
        $expense->title        = $data['title'];
        $expense->amount       = $data['amount'];
        $expense->expense_date = $data['expense_date'];
        $expense->notes        = $data['notes'] ?? null;

        // إذا عندك created_by بالجدول
        if (property_exists($expense, 'created_by') || \Schema::hasColumn('expenses', 'created_by')) {
            $expense->created_by = auth()->id();
        }

        $expense->save();

        return redirect()->route('finance.expenses.index')->with('status', 'Expense created ✅');
    }

    public function edit(Expense $expense)
    {
        return view('finance.expenses.edit', compact('expense'));
    }

    public function update(Request $request, Expense $expense)
    {
        $data = $request->validate([
            'title'        => 'required|string|max:255',
            'amount'       => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'notes'        => 'nullable|string',
        ]);

        $expense->title        = $data['title'];
        $expense->amount       = $data['amount'];
        $expense->expense_date = $data['expense_date'];
        $expense->notes        = $data['notes'] ?? null;

        $expense->save();

        return redirect()->route('finance.expenses.index')->with('status', 'Expense updated ✅');
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();
        return back()->with('status', 'Expense deleted ✅');
    }
}
