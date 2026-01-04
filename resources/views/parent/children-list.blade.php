@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-start">
            @include('layouts.left-menu')

            <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
                <div class="row pt-2">
                    <div class="col ps-4">

                        <h1 class="display-6 mb-3">
                            <i class="bi bi-people"></i> My Children
                        </h1>

                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                                <li class="breadcrumb-item active" aria-current="page">My Children</li>
                            </ol>
                        </nav>

                        @include('session-messages')

                        <div class="card p-3 shadow-sm">
                            <input type="text" id="searchInput" class="form-control mb-3"
                                   placeholder="ابحث باسم ابنك أو بريده...">

                            <table class="table table-bordered table-hover" id="childrenTable">
                                <thead class="table-light">
                                <tr>
                                    <th>Photo</th>
                                    <th>Child First Name</th>
                                    <th>Child Last Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th style="width:240px">Actions</th>
                                </tr>
                                </thead>

                                <tbody>
                                @forelse($children as $child)
                                    @php
                                        // يدعم الحالتين:
                                        // 1) $child هو StudentParentInfo وفيه relation student
                                        // 2) $child هو User(student) مباشرة
                                        $student = $child->student ?? $child;
                                    @endphp

                                    @if($student)
                                        <tr>
                                            <td>
                                                @if (!empty($student->photo))
                                                    <img src="{{ asset('/storage'.$student->photo) }}" width="35" class="rounded">
                                                @else
                                                    <i class="bi bi-person-square"></i>
                                                @endif
                                            </td>
                                            <td class="student-name">{{ $student->first_name }}</td>
                                            <td>{{ $student->last_name }}</td>
                                            <td class="student-email">{{ $student->email }}</td>
                                            <td>{{ $student->phone }}</td>
                                            <td class="d-flex gap-2 flex-wrap">
                                                <a href="{{ route('student.profile.show', $student->id) }}"
                                                   class="btn btn-sm btn-outline-primary">
                                                    View Profile
                                                </a>

                                                <a href="{{ \Illuminate\Support\Facades\Route::has('parent.reportcard.child')
                                                    ? route('parent.reportcard.child', $student->id)
                                                    : url('/parent/children/'.$student->id.'/report-card') }}"
                                                   class="btn btn-sm btn-outline-dark">
                                                    Report Card
                                                </a>
                                            </td>
                                        </tr>
                                    @endif
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">
                                            لا يوجد أبناء مرتبطين بحسابك حالياً.
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>

                @include('layouts.footer')
            </div>
        </div>
    </div>

    <script>
        document.getElementById('searchInput')?.addEventListener('keyup', function () {
            const term = (this.value || '').toLowerCase();
            const rows = document.querySelectorAll('#childrenTable tbody tr');

            rows.forEach(row => {
                const name  = row.querySelector('.student-name')?.textContent.toLowerCase() || '';
                const email = row.querySelector('.student-email')?.textContent.toLowerCase() || '';
                row.style.display = (name.includes(term) || email.includes(term)) ? '' : 'none';
            });
        });
    </script>
@endsection
