@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-start">
            @include('layouts.left-menu')

            <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
                <div class="row pt-2">
                    <div class="col ps-4">

                        <h1 class="display-6 mb-3">
                            <i class="bi bi-people"></i> Parent Dashboard
                        </h1>

                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Parent Dashboard</li>
                            </ol>
                        </nav>

                        @include('session-messages')

                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="card shadow-sm">
                                    <div class="card-body">
                                        <h6 class="card-subtitle mb-2 text-muted">Children</h6>
                                        <h3 class="card-title">{{ $childrenCount ?? 0 }}</h3>
                                        <p class="mb-0 small text-muted">Number of children linked to your account</p>

                                        <a class="btn btn-sm btn-outline-primary mt-2"
                                           href="{{ \Illuminate\Support\Facades\Route::has('parent.children') ? route('parent.children') : url('/parent/children') }}">
                                            View Children
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="card shadow-sm">
                                    <div class="card-body">
                                        <h6 class="card-subtitle mb-2 text-muted">Teachers</h6>
                                        <h3 class="card-title">{{ $teacherCount ?? 0 }}</h3>
                                        <p class="mb-0 small text-muted">Teachers teaching your active child</p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="card shadow-sm">
                                    <div class="card-body">
                                        <h6 class="card-subtitle mb-2 text-muted">Class & Section</h6>

                                        @if($activeChild && $promotion_info && $promotion_info->section && $promotion_info->section->schoolClass)
                                            <h5 class="card-title mb-1">
                                                {{ $promotion_info->section->schoolClass->class_name }}
                                                - {{ $promotion_info->section->section_name }}
                                            </h5>
                                            <p class="mb-0 small text-muted">
                                                Active child: {{ $activeChild->first_name }} {{ $activeChild->last_name }}
                                            </p>

                                            <a class="btn btn-sm btn-outline-dark mt-2"
                                               href="{{ \Illuminate\Support\Facades\Route::has('parent.reportcard.child') ? route('parent.reportcard.child', $activeChild->id) : url('/parent/children/'.$activeChild->id.'/report-card') }}">
                                                Open Report Card
                                            </a>
                                        @else
                                            <p class="mb-0 text-muted">No active child selected.</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-lg-7 mb-3">
                                <div class="card shadow-sm">
                                    <div class="card-header bg-transparent">
                                        <i class="bi bi-calendar-event me-2"></i> Events
                                    </div>
                                    <div class="card-body text-dark">
                                        @include('components.events.event-calendar', ['editable' => 'false', 'selectable' => 'false'])
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-5 mb-3">
                                <div class="card shadow-sm">
                                    <div class="card-header bg-transparent">
                                        <i class="bi bi-megaphone me-2"></i> Latest Notices
                                    </div>

                                    <div class="card-body">
                                        @forelse($notices as $notice)
                                            <div class="mb-3">
                                                <div class="small text-muted mb-1">
                                                    {{ optional($notice->created_at)->format('d M Y') }}
                                                </div>
                                                <div class="fw-semibold">
                                                    {{ \Illuminate\Support\Str::limit(strip_tags($notice->notice ?? ''), 140) }}
                                                </div>
                                            </div>

                                            @if(!$loop->last)
                                                <hr class="my-2">
                                            @endif
                                        @empty
                                            <p class="text-muted mb-0">No notices available.</p>
                                        @endforelse

                                        @if(isset($notices) && method_exists($notices, 'links'))
                                            <div class="mt-2">{{ $notices->links() }}</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            To view report cards for your children, go to
                            <strong>Exams & Grades</strong> → <strong>Children Report Cards</strong>
                            أو من <strong>My Children</strong>.
                        </div>

                    </div>
                </div>

                @include('layouts.footer')
            </div>
        </div>
    </div>
@endsection
