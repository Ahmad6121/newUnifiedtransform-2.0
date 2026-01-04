@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="mb-0"><i class="bi bi-pencil"></i> Edit Accountant</h2>
            <a href="{{ route('accountants.index') }}" class="btn btn-outline-secondary">
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

        <div class="card shadow-sm">
            <div class="card-body">
                <form method="POST" action="{{ route('accountants.update', $accountant->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">First Name</label>
                            <input type="text" name="first_name" class="form-control"
                                   value="{{ old('first_name', $accountant->first_name) }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="last_name" class="form-control"
                                   value="{{ old('last_name', $accountant->last_name) }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control"
                                   value="{{ old('email', $accountant->email) }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Phone (optional)</label>
                            <input type="text" name="phone" class="form-control"
                                   value="{{ old('phone', $accountant->phone) }}">
                        </div>


                        <div class="col-md-6">
                            <label class="form-label">New Password (optional)</label>
                            <input type="password" name="password" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" name="password_confirmation" class="form-control">
                        </div>

                        <div class="col-12">
                            <button class="btn btn-primary" type="submit">
                                <i class="bi bi-check2-circle"></i> Update
                            </button>
                        </div>
                    </div>

                </form>
            </div>
        </div>

    </div>
@endsection
