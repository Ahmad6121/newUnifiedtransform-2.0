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

                        {{-- 🔹 Cards أعلى الصفحة --}}
                        <div class="row mb-4">
                            {{-- عدد الأبناء --}}
                            <div class="col-md-4">
                                <div class="card shadow-sm">
                                    <div class="card-body">
                                        <h6 class="card-subtitle mb-2 text-muted">Children</h6>
                                        <h3 class="card-title">{{ $childrenCount ?? 0 }}</h3>
                                        <p class="mb-0 small text-muted">
                                            Number of children linked to your account
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {{-- عدد المعلّمين اللي بيدرّسوا الطفل النشط --}}
                            <div class="col-md-4">
                                <div class="card shadow-sm">
                                    <div class="card-body">
                                        <h6 class="card-subtitle mb-2 text-muted">Teachers</h6>
                                        <h3 class="card-title">{{ $teacherCount ?? 0 }}</h3>
                                        <p class="mb-0 small text-muted">
                                            Teachers teaching your active child
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {{-- الصف والشعبة للطفل النشط --}}
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
                                        @else
                                            <p class="mb-0 text-muted">No active child selected.</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- 🔹 Events + Notices --}}
                        <div class="row mb-4">
                            <div class="row row-cols-2 mt-4">
                                <div class="col">
                                    <div class="card mb-3">
                                        <div class="card-header bg-transparent"><i class="bi bi-calendar-event me-2"></i> Events</div>
                                        <div class="card-body text-dark">
                                            @include('components.events.event-calendar', ['editable' => 'false', 'selectable' => 'false'])
                                            {{-- <div class="overflow-auto" style="height: 250px;">
                                                <div class="list-group">
                                                    <a href="#" class="list-group-item list-group-item-action">
                                                        <div class="d-flex w-100 justify-content-between">
                                                        <h5 class="mb-1">List group item heading</h5>
                                                        <small>3 days ago</small>
                                                        </div>
                                                        <p class="mb-1">Some placeholder content in a paragraph.</p>
                                                        <small>And some small print.</small>
                                                    </a>
                                                </div>
                                            </div> --}}
                                        </div>
                                    </div>
                                </div>

                            <div class="col-md-5">
                                <div class="card shadow-sm mb-3">
                                    <div class="card-header">
                                        <i class="bi bi-megaphone"></i> Latest Notices
                                    </div>
                                    <div class="card-body">
                                        @forelse($notices as $notice)
                                            <div class="mb-2">
                                                <strong>{{ $notice->title ?? 'Notice' }}</strong>
                                                <div class="small text-muted">
                                                    {{ optional($notice->created_at)->format('d M Y') }}
                                                </div>
                                            </div>
                                            @if(!$loop->last)
                                                <hr class="my-2">
                                            @endif
                                        @empty
                                            <p class="text-muted mb-0">No notices available.</p>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ملاحظة قصيرة للأهل --}}
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            To view detailed information about your children, use the
                            <strong>Students → View Students</strong> menu on the left.
                            To see their teachers, use <strong>Teachers → View Teachers</strong>.
                        </div>

                    </div>
                </div>

                @include('layouts.footer')
            </div>
        </div>
    </div>
@endsection

