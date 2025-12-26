@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-start">
            @include('layouts.left-menu')

            <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
                <div class="row pt-2">
                    <div class="col ps-4">

                        <h1 class="display-6 mb-3"><i class="bi bi-megaphone"></i> Create Notice</h1>

                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Create Notice</li>
                            </ol>
                        </nav>

                        @include('session-messages')

                        <div class="row">
                            <form action="{{ route('notice.store') }}" method="POST">
                                @csrf

                                <input type="hidden" name="session_id" value="{{ $current_school_session_id }}">

                                {{-- Audience --}}
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Audience</label>
                                        <select name="audience_type" id="audience_type" class="form-select">
                                            <option value="all">All (Everyone)</option>
                                            <option value="roles" selected>By Roles</option>
                                            <option value="users">Specific Users</option>
                                        </select>
                                    </div>
                                </div>

                                {{-- By Roles (Pretty UI) --}}
                                <div class="row mb-3" id="aud_roles_box" style="display:none;">
                                    <div class="col-md-10">
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <label class="form-label mb-0">Select Roles</label>

                                            <div class="d-flex gap-2">
                                                <button type="button" class="btn btn-sm btn-outline-primary" id="roles_select_all">
                                                    Select all
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary" id="roles_clear_all">
                                                    Clear
                                                </button>
                                            </div>
                                        </div>

                                        <input type="text" class="form-control mb-2" id="roles_search" placeholder="Search roles...">

                                        @php
                                            // عدّل/زِد الرولز حسب مشروعك
                                            $roles = [
                                                ['slug' => 'teacher',     'label' => 'Teacher',     'icon' => 'bi-person-badge'],
                                                ['slug' => 'student',     'label' => 'Student',     'icon' => 'bi-mortarboard'],
                                                ['slug' => 'parent',      'label' => 'Parent',      'icon' => 'bi-people'],
                                                ['slug' => 'finance',     'label' => 'Finance',     'icon' => 'bi-cash-coin'],
                                                ['slug' => 'staff',       'label' => 'Staff',       'icon' => 'bi-person-workspace'],
                                                ['slug' => 'admin',       'label' => 'Admin',       'icon' => 'bi-shield-lock'],
                                                ['slug' => 'super_admin', 'label' => 'Super Admin', 'icon' => 'bi-shield-check'],
                                            ];

                                            // لو بدك admin/super_admin دايمًا يظهروا كـ checked وممنوع تغييرهم:
                                            $lockAdminRoles = false; // خلّيه true إذا بدكهم دايمًا مختارين ومقفولين بالواجهة
                                        @endphp

                                        <div class="row g-2" id="roles_grid">
                                            @foreach($roles as $r)
                                                @php
                                                    $locked = $lockAdminRoles && in_array($r['slug'], ['admin','super_admin']);
                                                @endphp

                                                <div class="col-12 col-sm-6 col-lg-4 role-item" data-role="{{ strtolower($r['label']) }} {{ $r['slug'] }}">
                                                    <label class="role-card d-flex align-items-center gap-2 p-3 border rounded-3 w-100"
                                                           style="cursor:pointer; user-select:none;">
                                                        <input
                                                            type="checkbox"
                                                            class="form-check-input m-0 role-checkbox"
                                                            name="audience_roles[]"
                                                            value="{{ $r['slug'] }}"
                                                            {{ $locked ? 'checked' : '' }}
                                                            {{ $locked ? 'disabled' : '' }}
                                                        >
                                                        <i class="bi {{ $r['icon'] }} fs-5 text-primary"></i>
                                                        <div class="d-flex flex-column">
                                                            <span class="fw-semibold">{{ $r['label'] }}</span>
                                                            <small class="text-muted">{{ $r['slug'] }}</small>
                                                        </div>
                                                        <span class="ms-auto badge bg-light text-dark border role-badge">Select</span>
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>

                                        <div class="form-text mt-2">
                                            اختر أكثر من Role بسهولة ✅
                                        </div>
                                    </div>
                                </div>

                                {{-- Specific Users --}}
                                <div class="row mb-3" id="aud_users_box" style="display:none;">
                                    <div class="col-md-8">
                                        <label class="form-label">Specific Users (IDs)</label>
                                        <input name="audience_users" class="form-control" placeholder="Example: 2,5,9">
                                        <div class="form-text">IDs separated by commas</div>
                                    </div>
                                </div>

                                <style>
                                    .role-card { transition: all .15s ease-in-out; background: #fff; }
                                    .role-card:hover { transform: translateY(-1px); box-shadow: 0 .25rem .75rem rgba(0,0,0,.06); }
                                    .role-card.is-selected { border-color: #0d6efd !important; background: rgba(13,110,253,.05); }
                                    .role-card.is-selected .role-badge { background: #0d6efd !important; color: #fff !important; border-color: #0d6efd !important; }
                                </style>

                                <script>
                                    (function(){
                                        const audienceType = document.getElementById('audience_type');
                                        const rolesBox = document.getElementById('aud_roles_box');
                                        const usersBox = document.getElementById('aud_users_box');

                                        const rolesGrid = document.getElementById('roles_grid');
                                        const rolesSearch = document.getElementById('roles_search');
                                        const selectAllBtn = document.getElementById('roles_select_all');
                                        const clearAllBtn  = document.getElementById('roles_clear_all');

                                        function syncAudienceBoxes(){
                                            rolesBox.style.display = (audienceType.value === 'roles') ? '' : 'none';
                                            usersBox.style.display = (audienceType.value === 'users') ? '' : 'none';
                                        }

                                        function refreshRoleCards(){
                                            const cards = rolesGrid.querySelectorAll('.role-item');
                                            cards.forEach(item => {
                                                const checkbox = item.querySelector('.role-checkbox');
                                                const card = item.querySelector('.role-card');
                                                if (!checkbox || !card) return;

                                                if (checkbox.checked) card.classList.add('is-selected');
                                                else card.classList.remove('is-selected');
                                            });
                                        }

                                        // click label already toggles checkbox; we just refresh UI after changes
                                        rolesGrid.addEventListener('change', function(e){
                                            if (e.target.classList.contains('role-checkbox')) refreshRoleCards();
                                        });

                                        // search filter
                                        rolesSearch.addEventListener('input', function(){
                                            const q = this.value.trim().toLowerCase();
                                            const items = rolesGrid.querySelectorAll('.role-item');
                                            items.forEach(it => {
                                                const hay = (it.getAttribute('data-role') || '').toLowerCase();
                                                it.style.display = hay.includes(q) ? '' : 'none';
                                            });
                                        });

                                        // select all / clear (doesn't touch disabled)
                                        selectAllBtn.addEventListener('click', function(){
                                            rolesGrid.querySelectorAll('.role-checkbox').forEach(cb => {
                                                if (!cb.disabled) cb.checked = true;
                                            });
                                            refreshRoleCards();
                                        });

                                        clearAllBtn.addEventListener('click', function(){
                                            rolesGrid.querySelectorAll('.role-checkbox').forEach(cb => {
                                                if (!cb.disabled) cb.checked = false;
                                            });
                                            refreshRoleCards();
                                        });

                                        audienceType.addEventListener('change', syncAudienceBoxes);

                                        // init
                                        syncAudienceBoxes();
                                        refreshRoleCards();
                                    })();
                                </script>


                                {{-- CKEditor --}}
                                @include('components.ckeditor.editor', ['name' => 'notice'])

                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="bi bi-check2"></i> Save
                                </button>
                            </form>
                        </div>

                    </div>
                </div>

                @include('layouts.footer')
            </div>
        </div>
    </div>

    <script>
        (function(){
            const t = document.getElementById('audience_type');
            const r = document.getElementById('aud_roles_box');
            const u = document.getElementById('aud_users_box');

            function sync(){
                r.style.display = (t.value === 'roles') ? '' : 'none';
                u.style.display = (t.value === 'users') ? '' : 'none';
            }

            if (t) {
                t.addEventListener('change', sync);
                sync(); // initial
            }
        })();
    </script>
@endsection
