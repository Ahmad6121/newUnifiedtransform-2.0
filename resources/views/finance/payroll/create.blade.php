@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>Create Payroll</h3>
            <a href="{{ route('finance.payroll.index') }}" class="btn btn-light">Back</a>
        </div>

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('finance.payroll.store') }}">
            @csrf
            @include('finance.payroll._form', ['payroll' => $payroll, 'employees' => $employees])
        </form>
    </div>
@endsection
