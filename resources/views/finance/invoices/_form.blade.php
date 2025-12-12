<div class="mb-3">
    <label class="form-label">Student</label>
    <select name="student_id" class="form-select" required>
        <option value="">-- Select Student --</option>
        @foreach($students as $s)
            <option value="{{ $s->id }}"
                @selected(old('student_id', $invoice->student_id ?? '') == $s->id)>
                {{ $s->first_name }} {{ $s->last_name }} (ID: {{ $s->id }})
            </option>
        @endforeach
    </select>
</div>

<div class="mb-3">
    <label class="form-label">Class (optional)</label>
    <select name="class_id" class="form-select">
        <option value="">-- None --</option>
        @foreach($classes as $c)
            <option value="{{ $c->id }}"
                @selected(old('class_id', $invoice->class_id ?? '') == $c->id)>
                {{ $c->class_name }}
            </option>
        @endforeach
    </select>
</div>

<div class="mb-3">
    <label class="form-label">Title</label>
    <input type="text" name="title" class="form-control"
           value="{{ old('title', $invoice->title ?? 'Tuition Fee') }}" required>
</div>

<div class="mb-3">
    <label class="form-label">Amount</label>
    <input type="number" step="0.01" min="0.01" name="amount" class="form-control"
           value="{{ old('amount', $invoice->amount ?? '') }}" required>
</div>

<div class="mb-3">
    <label class="form-label">Status</label>
    @php
        $statuses = ['unpaid'=>'Unpaid','partial'=>'Partial','paid'=>'Paid','overdue'=>'Overdue'];
        $current  = old('status', $invoice->status ?? 'unpaid');
    @endphp
    <select name="status" class="form-select" required>
        @foreach($statuses as $k=>$v)
            <option value="{{ $k }}" @selected($current === $k)>{{ $v }}</option>
        @endforeach
    </select>
</div>

<div class="mb-3">
    <label class="form-label">Due date</label>
    <input type="date" name="due_date" class="form-control"
           value="{{ old('due_date', optional($invoice->due_date)->format('Y-m-d')) }}">
</div>

<div class="mb-3">
    <label class="form-label">Notes</label>
    <textarea name="notes" class="form-control" rows="3">{{ old('notes', $invoice->notes ?? '') }}</textarea>
</div>

<div class="d-flex gap-2">
    <button class="btn btn-primary" type="submit">
        {{ isset($invoice->id) ? 'Update' : 'Create' }}
    </button>
    <a href="{{ route('finance.invoices.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>
