@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="mb-0">Add Expense</h3>
            <a href="{{ route('finance.expenses.index') }}" class="btn btn-outline-secondary">Back</a>
        </div>

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                </ul>
            </div>
        @endif

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <form method="POST" action="{{ route('finance.expenses.store') }}">
                    @csrf
                    @include('finance.expenses._form', ['expense' => $expense])
                </form>
            </div>
        </div>
    </div>
@endsection
