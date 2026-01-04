@php
    // PHP 7.4 safe defaults
    $selectedRef = old('employee_ref', isset($payroll) ? ($payroll->employee_ref ?? '') : '');
    $titleVal    = old('title', isset($payroll) ? ($payroll->title ?? '') : '');
    $amountVal   = old('amount', isset($payroll) ? ($payroll->amount ?? '') : '');

    $dateVal = old('payroll_date');
    if (!$dateVal && isset($payroll) && !empty($payroll->payroll_date)) {
        try {
            $dateVal = \Carbon\Carbon::parse($payroll->payroll_date)->format('Y-m-d');
        } catch (\Exception $e) {
            $dateVal = '';
        }
    }

    $notesVal = old('notes', isset($payroll) ? ($payroll->notes ?? '') : '');

    // group employees for optgroup
    $groups = [];
    foreach ($employees as $e) {
        $g = $e['group'] ?? 'Employees';
        if (!isset($groups[$g])) $groups[$g] = [];
        $groups[$g][] = $e;
    }
@endphp

<div class="card shadow-sm border-0">
    <div class="card-body">

        <div class="mb-3">
            <label class="form-label fw-bold">Employee</label>
            <select name="employee_ref" class="form-control" required>
                <option value="">-- Select Employee --</option>

                @foreach($groups as $gName => $items)
                    <optgroup label="{{ $gName }}">
                        @foreach($items as $it)
                            @php
                                $ref = ($it['type'] ?? '') . '|' . ($it['id'] ?? '');
                                $label = $it['name'] ?? $ref;
                            @endphp
                            <option value="{{ $ref }}" {{ (string)$selectedRef === (string)$ref ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </optgroup>
                @endforeach

            </select>
            <small class="text-muted">Teachers + Accountants + Staff</small>
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">Title</label>
            <input type="text" name="title" class="form-control" value="{{ $titleVal }}" required>
        </div>

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-bold">Amount</label>
                <input type="number" step="0.01" name="amount" class="form-control" value="{{ $amountVal }}" required>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-bold">Payroll Date</label>
                <input type="date" name="payroll_date" class="form-control" value="{{ $dateVal }}" required>
            </div>
        </div>

        <div class="mt-3">
            <label class="form-label fw-bold">Notes (optional)</label>
            <textarea name="notes" rows="3" class="form-control">{{ $notesVal }}</textarea>
        </div>

        <div class="text-end mt-3">
            <button class="btn btn-primary">
                {{ (isset($payroll) && isset($payroll->id)) ? 'Update Payroll' : 'Create Payroll' }}
            </button>
        </div>

    </div>
</div>
