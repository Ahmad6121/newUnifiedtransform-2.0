@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h2 class="mb-0">Grade Rules</h2>
            <a class="btn btn-primary" href="{{ route('exam.grade.system.rule.create') }}">+ Add Rule</a>
        </div>

        @if(session('success'))
            <div class="alert alert-success mb-3">{{ session('success') }}</div>
        @endif

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead>
                        <tr>
                            <th>System</th>
                            <th>Min %</th>
                            <th>Max %</th>
                            <th>Grade</th>
                            <th>Remark</th>
                            <th style="width:120px;">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($rules as $r)
                            <tr>
                                <td>{{ $r->system->name ?? '-' }}</td>
                                <td>{{ $r->min_percent }}</td>
                                <td>{{ $r->max_percent }}</td>
                                <td>{{ $r->grade }}</td>
                                <td>{{ $r->remark ?? '-' }}</td>
                                <td>
                                    <form method="POST" action="{{ route('exam.grade.system.rule.delete') }}">
                                        @csrf
                                        <input type="hidden" name="id" value="{{ $r->id }}">
                                        <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this rule?')">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-muted">No grade rules.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
@endsection

