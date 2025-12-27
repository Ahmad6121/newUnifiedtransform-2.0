@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">Manage User Permissions</h1>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>

            </div>

            <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>


        {{-- 🔍 مربع البحث --}}
        <div class="row mb-3">
            <div class="col-md-6">
                <input type="text" id="searchInput" class="form-control" placeholder="Search users by name or email">
            </div>
            <div class="col-md-3">
                <select id="sortSelect" class="form-select">
                    <option value="">Sort by</option>
                    <option value="name_asc">Name (A-Z)</option>
                    <option value="name_desc">Name (Z-A)</option>
                    <option value="role_asc">Roles (A-Z)</option>
                    <option value="role_desc">Roles (Z-A)</option>
                </select>
            </div>
        </div>

        {{-- 🧾 جدول المستخدمين --}}
        <table class="table table-hover table-bordered" id="usersTable">
            <thead class="table-light">
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Current Roles</th>
                <th width="150">Actions</th>
            </tr>
            </thead>
            <tbody>
            {{-- سيتم تعبئته ديناميكياً بالـ JavaScript --}}
            </tbody>
        </table>
    </div>

    {{-- 📜 سكربت البحث والفرز باستخدام Ajax --}}
    <script>
        const searchInput = document.getElementById('searchInput');
        const sortSelect = document.getElementById('sortSelect');
        const tableBody = document.querySelector('#usersTable tbody');

        let usersData = [];

        // جلب جميع المستخدمين عند تحميل الصفحة
        async function fetchUsers(query = '') {
            const res = await fetch(`{{ route('admin.users.search') }}?q=${query}`);
            usersData = await res.json();
            renderTable(usersData);
        }

        // دالة لعرض المستخدمين داخل الجدول
        function renderTable(data) {
            tableBody.innerHTML = '';
            data.forEach(user => {
                const roles = user.roles.length ? user.roles.map(r => r.name).join(', ') : 'لا يوجد';
                tableBody.innerHTML += `
                <tr>
                    <td class="user-name">${user.first_name} ${user.last_name}</td>
                    <td class="user-email">${user.email}</td>
                    <td class="user-role">${roles}</td>
                    <td>
                        <a href="/admin/users/${user.id}/edit" class="btn btn-sm btn-primary">
                            <i class="bi bi-pencil-square"></i> Actions
                        </a>
                    </td>
                </tr>`;
            });
        }

        // البحث الحي
        searchInput.addEventListener('keyup', function () {
            fetchUsers(this.value);
        });

        // الفرز
        sortSelect.addEventListener('change', function () {
            const value = this.value;
            usersData.sort((a, b) => {
                const nameA = (a.first_name + ' ' + a.last_name).toLowerCase();
                const nameB = (b.first_name + ' ' + b.last_name).toLowerCase();
                const roleA = a.roles.map(r => r.name).join(', ').toLowerCase();
                const roleB = b.roles.map(r => r.name).join(', ').toLowerCase();

                switch (value) {
                    case 'name_asc': return nameA.localeCompare(nameB);
                    case 'name_desc': return nameB.localeCompare(nameA);
                    case 'role_asc': return roleA.localeCompare(roleB);
                    case 'role_desc': return roleB.localeCompare(roleA);
                    default: return 0;
                }
            });

            renderTable(usersData);
        });

        // تحميل البيانات عند فتح الصفحة
        fetchUsers();
    </script>
@endsection
