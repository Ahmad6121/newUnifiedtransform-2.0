@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-start">
            @include('layouts.left-menu')

            <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
                <div class="row pt-2">
                    <div class="col ps-4">
                        <h1 class="display-6 mb-3">
                            <i class="bi bi-graph-up"></i> Student Progress
                        </h1>

                        @if(!empty($activeChild))
                            <p>
                                Here you will be able to see marks & attendance for:
                                <strong>{{ $activeChild->first_name }} {{ $activeChild->last_name }}</strong>
                            </p>

                            <div class="d-flex gap-2 mb-3">
                                <a class="btn btn-outline-dark btn-sm"
                                   href="{{ \Illuminate\Support\Facades\Route::has('parent.reportcard.child') ? route('parent.reportcard.child', $activeChild->id) : url('/parent/children/'.$activeChild->id.'/report-card') }}">
                                    Open Report Card
                                </a>
                                <a class="btn btn-outline-primary btn-sm"
                                   href="{{ \Illuminate\Support\Facades\Route::has('parent.children') ? route('parent.children') : url('/parent/children') }}">
                                    Back to Children
                                </a>
                            </div>
                        @else
                            <div class="alert alert-warning">No child linked / selected.</div>
                        @endif

                        <div class="alert alert-info">
                            This page is a placeholder. Later we can connect it to marks & attendance tables.
                        </div>
                    </div>
                </div>

                @include('layouts.footer')
            </div>
        </div>
    </div>
@endsection
