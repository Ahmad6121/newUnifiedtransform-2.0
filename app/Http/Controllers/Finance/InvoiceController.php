<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\User;
use App\Models\SchoolClass;
use App\Models\SchoolSession;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index()
    {
        $invoices = Invoice::with(['student','class'])->latest()->paginate(15);
        return view('finance.invoices.index', compact('invoices'));
    }

    public function create()
    {
        $invoice   = new Invoice(); // مهم عشان الفورم المشترك
        $students  = User::where('role', 'student')->orderBy('first_name')->get(['id','first_name','last_name']);
        $classes   = SchoolClass::orderBy('class_name')->get(['id','class_name']);

        return view('finance.invoices.create', compact('invoice','students','classes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'student_id' => ['required','exists:users,id'],
            'class_id'   => ['nullable','exists:school_classes,id'],
            'title'      => ['required','string','max:255'],
            'amount'     => ['required','numeric','min:0.01'],
            'status'     => ['required','in:unpaid,partial,paid,overdue'],
            'due_date'   => ['nullable','date'],
            'notes'      => ['nullable','string'],
        ]);

        // لازم session_id (غير قابل للـ NULL في المايغريشن)
        $sessionId = session('browse_session_id') ?? SchoolSession::latest()->value('id');
        if (!$sessionId) {
            return back()->withErrors('No school session found. Seed SchoolSession first.')->withInput();
        }

        $data['session_id'] = $sessionId;

        Invoice::create($data);

        return redirect()->route('finance.invoices.index')->with('status','Invoice created successfully.');
    }

    public function edit(Invoice $invoice)
    {
        $students  = User::where('role', 'student')->orderBy('first_name')->get(['id','first_name','last_name']);
        $classes   = SchoolClass::orderBy('class_name')->get(['id','class_name']);

        return view('finance.invoices.edit', compact('invoice','students','classes'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        $data = $request->validate([
            'student_id' => ['required','exists:users,id'],
            'class_id'   => ['nullable','exists:school_classes,id'],
            'title'      => ['required','string','max:255'],
            'amount'     => ['required','numeric','min:0.01'],
            'status'     => ['required','in:unpaid,partial,paid,overdue'],
            'due_date'   => ['nullable','date'],
            'notes'      => ['nullable','string'],
        ]);

        $invoice->update($data);

        return redirect()->route('finance.invoices.index')->with('status','Invoice updated successfully.');
    }

    public function destroy(Invoice $invoice)
    {
        $invoice->delete();
        return back()->with('status','Invoice deleted.');
    }

    // (اختياري)
    public function show(Invoice $invoice)
    {
        $invoice->load(['student','class','payments']);
        return view('finance.invoices.show', compact('invoice'));
    }
}

