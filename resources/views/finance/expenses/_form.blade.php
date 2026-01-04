@php
    // PHP 7.4 safe date value for expense_date
    $expenseDate = old('expense_date');

    if (!$expenseDate && isset($expense) && !empty($expense->expense_date)) {
        try {
            $expenseDate = \Carbon\Carbon::parse($expense->expense_date)->format('Y-m-d');
        } catch (\Exception $e) {
            $expenseDate = '';
        }
    }
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label fw-bold">Title</label>
        <input type="text" name="title" class="form-control"
               value="{{ old('title', isset($expense) ? ($expense->title ?? '') : '') }}" required>
    </div>

    <div class="col-md-3">
        <label class="form-label fw-bold">Amount</label>
        <input type="number" step="0.01" name="amount" class="form-control"
               value="{{ old('amount', isset($expense) ? ($expense->amount ?? '') : '') }}" required>
    </div>

    <div class="col-md-3">
        <label class="form-label fw-bold">Expense Date</label>
        <input type="date" name="expense_date" class="form-control"
               value="{{ $expenseDate }}" required>
    </div>

    <div class="col-12">
        <label class="form-label fw-bold">Notes (optional)</label>
        <textarea name="notes" rows="4" class="form-control">{{ old('notes', isset($expense) ? ($expense->notes ?? '') : '') }}</textarea>
    </div>

    <div class="col-12 text-end">
        <button class="btn btn-primary">
            {{ (isset($expense) && isset($expense->id)) ? 'Update Expense' : 'Create Expense' }}
        </button>
    </div>
</div>
