@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>Edit Invoice #{{ $invoice->id }}</h3>
            <a href="{{ route('finance.invoices.index') }}" class="btn btn-light">Back</a>
        </div>

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('finance.invoices.update', $invoice) }}">
            @csrf
            @method('PUT')
            @include('finance.invoices._form', ['invoice' => $invoice, 'students' => $students, 'classes' => $classes])
        </form>
    </div>
@endsection
