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
                                <i class="bi bi-briefcase"></i> Job Titles
                            </h1>
                            <a href="{{ route('staff.job-titles.create') }}" class="btn btn-sm btn-primary">
                                <i class="bi bi-plus-circle"></i> Add Job Title
                            </a>
                        </div>

                        @if (session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif
                        @if (session('status'))
                            <div class="alert alert-success">{{ session('status') }}</div>
                        @endif
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="p-3 bg-white border shadow-sm">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                <tr>
                                    <th>Name</th>
                                    <th style="width:180px;">Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($titles as $t)
                                    <tr>
                                        <td>{{ $t->name }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a class="btn btn-sm btn-outline-primary"
                                                   href="{{ route('staff.job-titles.edit', $t->id) }}">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </a>

                                                <form action="{{ route('staff.job-titles.destroy', $t->id) }}"
                                                      method="POST"
                                                      onsubmit="return confirm('Delete this job title?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-sm btn-outline-danger" type="submit">
                                                        <i class="bi bi-trash"></i> Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-muted">No job titles found.</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>

                            <div class="mt-3">
                                {{ $titles->links() }}
                            </div>
                        </div>

                    </div>
                </div>

                @include('layouts.footer')
            </div>
        </div>
    </div>
@endsection
