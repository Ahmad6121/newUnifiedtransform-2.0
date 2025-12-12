@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>تعديل صلاحيات: {{ $user->first_name }} {{ $user->last_name }}</h1>

        <form method="POST" action="{{ route('admin.users.update', $user) }}">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label">اختر الأدوار:</label><br>
                @foreach($roles as $role)
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="roles[]" value="{{ $role->name }}"
                            {{ $user->hasRole($role->name) ? 'checked' : '' }}>
                        <label class="form-check-label">{{ $role->name }}</label>
                    </div>
                @endforeach
            </div>

            <button type="submit" class="btn btn-success">حفظ</button>
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">رجوع</a>
        </form>
    </div>
@endsection
