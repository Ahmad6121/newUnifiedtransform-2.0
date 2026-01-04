@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-start">
            @include('layouts.left-menu')

            <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
                <div class="row pt-2">
                    <div class="col ps-4">

                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h1 class="display-6 mb-0">
                                <i class="bi bi-pencil"></i> Edit Staff
                            </h1>
                            <a href="{{ route('staff.employees.index') }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Back
                            </a>
                        </div>

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                                </ul>
                            </div>
                        @endif

                        @php
                            // ✅ PHP 7.4 safe join_date formatting
                            $joinDateVal = old('join_date');
                            if (!$joinDateVal && isset($employee) && !empty($employee->join_date)) {
                                try {
                                    $joinDateVal = \Carbon\Carbon::parse($employee->join_date)->format('Y-m-d');
                                } catch (\Exception $e) {
                                    $joinDateVal = '';
                                }
                            }
                        @endphp

                        <div class="p-3 bg-white border shadow-sm">
                            <form method="POST" action="{{ route('staff.employees.update', ['employee' => $employee->id]) }}">
                                @csrf
                                @method('PUT')

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">First Name</label>
                                        <input type="text" name="first_name"
                                               value="{{ old('first_name', $employee->first_name) }}"
                                               class="form-control @error('first_name') is-invalid @enderror" required>
                                        @error('first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Last Name</label>
                                        <input type="text" name="last_name"
                                               value="{{ old('last_name', $employee->last_name) }}"
                                               class="form-control @error('last_name') is-invalid @enderror" required>
                                        @error('last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Email (optional)</label>
                                        <input type="email" name="email"
                                               value="{{ old('email', $employee->email) }}"
                                               class="form-control @error('email') is-invalid @enderror">
                                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Phone (optional)</label>
                                        <input type="text" name="phone"
                                               value="{{ old('phone', $employee->phone) }}"
                                               class="form-control @error('phone') is-invalid @enderror">
                                        @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Job Title (optional)</label>
                                        <select name="job_title_id" class="form-select @error('job_title_id') is-invalid @enderror">
                                            <option value="">-- None --</option>
                                            @foreach($jobTitles as $jt)
                                                <option value="{{ $jt->id }}"
                                                    {{ (string)old('job_title_id', $employee->job_title_id) === (string)$jt->id ? 'selected' : '' }}>
                                                    {{ $jt->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('job_title_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">Salary Type</label>
                                        <select name="salary_type" class="form-select @error('salary_type') is-invalid @enderror" required>
                                            <option value="fixed"  {{ old('salary_type', $employee->salary_type)=='fixed' ? 'selected' : '' }}>Fixed</option>
                                            <option value="hourly" {{ old('salary_type', $employee->salary_type)=='hourly' ? 'selected' : '' }}>Hourly</option>
                                        </select>
                                        @error('salary_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">Base Salary</label>
                                        <input type="number" step="0.01" min="0" name="base_salary"
                                               value="{{ old('base_salary', $employee->base_salary) }}"
                                               class="form-control @error('base_salary') is-invalid @enderror" required>
                                        @error('base_salary') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">Join Date (optional)</label>
                                        <input type="date" name="join_date"
                                               value="{{ $joinDateVal }}"
                                               class="form-control @error('join_date') is-invalid @enderror">
                                        @error('join_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">Status</label>
                                        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                            <option value="active"   {{ old('status', $employee->status)=='active' ? 'selected' : '' }}>Active</option>
                                            <option value="inactive" {{ old('status', $employee->status)=='inactive' ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>

                                <div class="mt-3">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="bi bi-check2-circle"></i> Update
                                    </button>
                                </div>
                            </form>
                        </div>

                    </div>
                </div>
                @include('layouts.footer')
            </div>
        </div>
    </div>
@endsection
