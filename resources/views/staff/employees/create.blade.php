@extends('layouts.app')

@section('content')
    <div class="container-fluid">

        <div class="d-flex align-items-center justify-content-between mb-3">
            <h2 class="mb-0">
                <i class="bi bi-person-plus me-2"></i> Add Staff
            </h2>

            <a href="{{ route('staff.employees.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">

                <form method="POST" action="{{ route('staff.employees.store') }}">
                    @csrf

                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label">First Name</label>
                            <input type="text" name="first_name" class="form-control"
                                   value="{{ old('first_name') }}" required>
                            @error('first_name') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="last_name" class="form-control"
                                   value="{{ old('last_name') }}" required>
                            @error('last_name') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Email (optional)</label>
                            <input type="email" name="email" class="form-control"
                                   value="{{ old('email') }}">
                            @error('email') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Phone (optional)</label>
                            <input type="text" name="phone" class="form-control"
                                   value="{{ old('phone') }}">
                            @error('phone') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Job Title (optional)</label>
                            <select name="job_title_id" class="form-select" id="job_title_id">
                                <option value="">-- None --</option>
                                @foreach($jobTitles ?? [] as $jt)
                                    <option value="{{ $jt->id }}" {{ old('job_title_id') == $jt->id ? 'selected' : '' }}>
                                        {{ $jt->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('job_title_id') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Salary Type</label>
                            <select name="salary_type" class="form-select">
                                <option value="fixed"  {{ old('salary_type','fixed')=='fixed' ? 'selected' : '' }}>Fixed</option>
                                <option value="hourly" {{ old('salary_type')=='hourly' ? 'selected' : '' }}>Hourly</option>
                            </select>
                            @error('salary_type') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Base Salary</label>
                            <input type="number" step="0.01" name="base_salary" class="form-control"
                                   value="{{ old('base_salary', 0) }}">
                            @error('base_salary') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Join Date (optional)</label>
                            <input type="date" name="join_date" class="form-control"
                                   value="{{ old('join_date') }}">
                            @error('join_date') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="active"   {{ old('status','active')=='active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status')=='inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>



                        <div id="login_fields" class="row g-3 mt-0 d-none">
                            <div class="col-md-6">
                                <label class="form-label">Login Email</label>
                                <input type="email" name="login_email" class="form-control"
                                       value="{{ old('login_email') }}">
                                @error('login_email') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Login Password</label>
                                <input type="password" name="login_password" class="form-control">
                                @error('login_password') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        {{-- ========================================== --}}

                        <div class="col-12 mt-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check2-circle me-1"></i> Save
                            </button>
                        </div>

                    </div>
                </form>

            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const jobTitleSelect = document.getElementById('job_title_id');
            const checkbox = document.getElementById('create_login');
            const fields = document.getElementById('login_fields');

            if (!jobTitleSelect || !checkbox || !fields) return;

            function isAccountantSelected() {
                const opt = jobTitleSelect.options[jobTitleSelect.selectedIndex];
                const text = (opt ? opt.text : '').trim();
                return text === 'Accountant';
            }

            function refreshLoginUI() {
                const ok = isAccountantSelected();

                if (!ok) {
                    checkbox.checked = false;
                    checkbox.disabled = true;
                    fields.classList.add('d-none');
                    return;
                }

                checkbox.disabled = false;
                if (checkbox.checked) fields.classList.remove('d-none');
                else fields.classList.add('d-none');
            }

            checkbox.addEventListener('change', refreshLoginUI);
            jobTitleSelect.addEventListener('change', refreshLoginUI);

            refreshLoginUI();
        });
    </script>
@endpush
