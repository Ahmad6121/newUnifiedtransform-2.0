@php
    // ✅ Session id (مهم عشان validation session_id required)
    $formSessionId = old('session_id', isset($sessionId) ? $sessionId : (isset($invoice) ? ($invoice->session_id ?? '') : ''));

    // ✅ Selected values (Create/Edit + old())
    $selectedClassId   = old('class_id', isset($invoice) ? ($invoice->class_id ?? '') : '');
    $selectedSectionId = old('section_id', isset($invoice) ? (property_exists($invoice, 'section_id') ? ($invoice->section_id ?? '') : '') : '');
    $selectedStudentId = old('student_id', isset($invoice) ? ($invoice->student_id ?? '') : '');

    // ✅ Due date for PHP 7.4
    $due = isset($invoice) ? ($invoice->due_date ?? null) : null;
    $dueDateValue = old('due_date', $due ? \Carbon\Carbon::parse($due)->format('Y-m-d') : '');
@endphp

{{-- ✅ Hidden session_id --}}
<input type="hidden" name="session_id" value="{{ $formSessionId }}">

<div class="card shadow-sm border-0">
    <div class="card-body">

        {{-- ✅ Class --}}
        <div class="mb-3">
            <label class="form-label fw-bold">Class</label>
            <select name="class_id" id="class_id" class="form-control">
                <option value="">-- Select Class --</option>
                @foreach($classes as $c)
                    @php
                        $label = $c->name ?? $c->class_name ?? $c->title ?? ('Class #' . $c->id);
                    @endphp
                    <option value="{{ $c->id }}" {{ (string)$selectedClassId === (string)$c->id ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
            <small class="text-muted">اختر الصف أولًا ليتم تحميل الشعب والطلاب.</small>
        </div>

        {{-- ✅ Section (filtered by class) --}}
        <div class="mb-3">
            <label class="form-label fw-bold">Section</label>
            <select name="section_id" id="section_id" class="form-control" {{ $selectedClassId ? '' : 'disabled' }}>
                <option value="">{{ $selectedClassId ? '-- Select Section --' : '-- Select Class First --' }}</option>

                {{-- في حالة Edit أو رجوع بعد Validation: ممكن يكون عندك $sections --}}
                @if(isset($sections) && count($sections))
                    @foreach($sections as $s)
                        @php
                            $sName = is_array($s) ? ($s['name'] ?? '') : ($s->name ?? $s->section_name ?? ('Section #' . $s->id));
                            $sId   = is_array($s) ? ($s['id'] ?? '') : $s->id;
                        @endphp
                        <option value="{{ $sId }}" {{ (string)$selectedSectionId === (string)$sId ? 'selected' : '' }}>
                            {{ $sName }}
                        </option>
                    @endforeach
                @endif
            </select>
        </div>

        {{-- ✅ Student (filtered by class + section) --}}
        <div class="mb-3">
            <label class="form-label fw-bold">Student</label>
            <select name="student_id" id="student_id" class="form-control" {{ ($selectedClassId && $selectedSectionId) ? '' : 'disabled' }}>
                <option value="">
                    {{ ($selectedClassId && $selectedSectionId) ? '-- Select Student --' : '-- Select Class & Section First --' }}
                </option>

                {{-- في حالة Edit أو رجوع بعد Validation: ممكن يكون عندك $students --}}
                @if(isset($students) && count($students))
                    @foreach($students as $st)
                        @php
                            $stName = is_array($st) ? ($st['name'] ?? '') : (trim(($st->first_name ?? '').' '.($st->last_name ?? '')) ?: ($st->name ?? ('Student #' . $st->id)));
                            $stId   = is_array($st) ? ($st['id'] ?? '') : $st->id;
                        @endphp
                        <option value="{{ $stId }}" {{ (string)$selectedStudentId === (string)$stId ? 'selected' : '' }}>
                            {{ $stName }}
                        </option>
                    @endforeach
                @endif
            </select>
        </div>

        {{-- Title --}}
        <div class="mb-3">
            <label class="form-label fw-bold">Title</label>
            <input type="text" name="title" class="form-control"
                   value="{{ old('title', isset($invoice) ? ($invoice->title ?? '') : '') }}">
        </div>

        {{-- Amount --}}
        <div class="mb-3">
            <label class="form-label fw-bold">Amount</label>
            <input type="number" step="0.01" name="amount" class="form-control"
                   value="{{ old('amount', isset($invoice) ? ($invoice->amount ?? '') : '') }}">
        </div>

        {{-- Due Date --}}
        <div class="mb-3">
            <label class="form-label fw-bold">Due Date</label>
            <input type="date" name="due_date" class="form-control" value="{{ $dueDateValue }}">
        </div>

        {{-- Status --}}
        <div class="mb-3">
            <label class="form-label fw-bold">Status</label>
            @php
                $st = old('status', isset($invoice) ? ($invoice->status ?? 'unpaid') : 'unpaid');
            @endphp
            <select name="status" class="form-control">
                <option value="unpaid"  {{ $st === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                <option value="partial" {{ $st === 'partial' ? 'selected' : '' }}>Partial</option>
                <option value="paid"    {{ $st === 'paid' ? 'selected' : '' }}>Paid</option>
            </select>
        </div>

        {{-- Notes --}}
        <div class="mb-3">
            <label class="form-label fw-bold">Notes (optional)</label>
            <textarea name="notes" class="form-control" rows="3">{{ old('notes', isset($invoice) ? ($invoice->notes ?? '') : '') }}</textarea>
        </div>

        {{-- Paid Amount --}}
        <div class="mb-3">
            <label class="form-label fw-bold">Paid Amount (optional)</label>
            <input type="number" step="0.01" name="paid_amount" class="form-control"
                   value="{{ old('paid_amount', isset($invoice) ? ($invoice->paid_amount ?? 0) : 0) }}">
            <small class="text-muted">Leave 0 for new unpaid invoices.</small>
        </div>

        <div class="text-end">
            <button class="btn btn-primary">
                {{ (isset($invoice) && isset($invoice->id)) ? 'Update Invoice' : 'Create Invoice' }}
            </button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var classSelect   = document.getElementById('class_id');
        var sectionSelect = document.getElementById('section_id');
        var studentSelect = document.getElementById('student_id');

        var sessionId = "{{ $formSessionId }}";

        var ajaxSectionsUrl = "{{ route('finance.invoices.ajaxSections') }}";
        var ajaxStudentsUrl = "{{ route('finance.invoices.ajaxStudents') }}";

        var selectedSectionId = "{{ (string)$selectedSectionId }}";
        var selectedStudentId = "{{ (string)$selectedStudentId }}";

        function resetSelect(selectEl, placeholder) {
            while (selectEl.options.length > 0) selectEl.remove(0);
            var opt = document.createElement('option');
            opt.value = '';
            opt.textContent = placeholder;
            selectEl.appendChild(opt);
        }

        function loadSections(classId, preselect) {
            sectionSelect.disabled = true;
            studentSelect.disabled = true;

            resetSelect(sectionSelect, '-- Loading Sections... --');
            resetSelect(studentSelect, '-- Select Class & Section First --');

            if (!classId) {
                resetSelect(sectionSelect, '-- Select Class First --');
                sectionSelect.disabled = true;
                return;
            }

            fetch(ajaxSectionsUrl + '?class_id=' + encodeURIComponent(classId) + '&session_id=' + encodeURIComponent(sessionId))
                .then(function (res) { return res.json(); })
                .then(function (items) {
                    resetSelect(sectionSelect, '-- Select Section --');

                    if (!items || !items.length) {
                        var opt = document.createElement('option');
                        opt.value = '';
                        opt.textContent = 'No sections found for this class';
                        sectionSelect.appendChild(opt);
                        sectionSelect.disabled = false;
                        return;
                    }

                    items.forEach(function (it) {
                        var o = document.createElement('option');
                        o.value = it.id;
                        o.textContent = it.name;
                        if (preselect && String(preselect) === String(it.id)) o.selected = true;
                        sectionSelect.appendChild(o);
                    });

                    sectionSelect.disabled = false;

                    // لو في Section محددة (Edit/Old) حمّل الطلاب مباشرة
                    if (preselect) {
                        loadStudents(classId, preselect, selectedStudentId);
                    }
                })
                .catch(function () {
                    resetSelect(sectionSelect, '-- Select Section --');
                    sectionSelect.disabled = false;
                });
        }

        function loadStudents(classId, sectionId, preselectStudent) {
            studentSelect.disabled = true;
            resetSelect(studentSelect, '-- Loading Students... --');

            if (!classId || !sectionId) {
                resetSelect(studentSelect, '-- Select Class & Section First --');
                studentSelect.disabled = true;
                return;
            }

            fetch(ajaxStudentsUrl + '?class_id=' + encodeURIComponent(classId) + '&section_id=' + encodeURIComponent(sectionId) + '&session_id=' + encodeURIComponent(sessionId))
                .then(function (res) { return res.json(); })
                .then(function (items) {
                    resetSelect(studentSelect, '-- Select Student --');

                    if (!items || !items.length) {
                        var opt = document.createElement('option');
                        opt.value = '';
                        opt.textContent = 'No students found for this class/section';
                        studentSelect.appendChild(opt);
                        studentSelect.disabled = false;
                        return;
                    }

                    items.forEach(function (it) {
                        var o = document.createElement('option');
                        o.value = it.id;
                        o.textContent = it.name;
                        if (preselectStudent && String(preselectStudent) === String(it.id)) o.selected = true;
                        studentSelect.appendChild(o);
                    });

                    studentSelect.disabled = false;
                })
                .catch(function () {
                    resetSelect(studentSelect, '-- Select Student --');
                    studentSelect.disabled = false;
                });
        }

        // Events
        classSelect.addEventListener('change', function () {
            selectedStudentId = '';
            loadSections(this.value, '');
        });

        sectionSelect.addEventListener('change', function () {
            loadStudents(classSelect.value, this.value, '');
        });

        // Init (Edit/Old values)
        if (classSelect.value) {
            loadSections(classSelect.value, selectedSectionId);
        }
    });
</script>
