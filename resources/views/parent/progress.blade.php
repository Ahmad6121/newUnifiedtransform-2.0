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

                        <p>Here you will be able to see marks & attendance for:
                            <strong>{{ $student->first_name }} {{ $student->last_name }}</strong>
                        </p>

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
