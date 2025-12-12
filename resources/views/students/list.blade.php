@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-start">
            @include('layouts.left-menu')
            <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
                <div class="row pt-2">
                    <div class="col ps-4">
                        <h1 class="display-6 mb-3">
                            <i class="bi bi-person-lines-fill"></i> Student List
                        </h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{route('home')}}">Home</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Student List</li>
                            </ol>
                        </nav>

                        @include('session-messages')

                        {{-- ✅ زر إضافة طالب للأدمن فقط --}}
                        @can('create students')
                            <div class="mb-3">
                                <a href="{{ route('student.create.show') }}" class="btn btn-primary">
                                    <i class="bi bi-person-plus"></i> إضافة طالب جديد
                                </a>
                            </div>
                        @endcan

                        <h6>Filter list by:</h6>
                        <div class="mb-4 mt-4">
                            <form class="row" action="{{route('student.list.show')}}" method="GET">
                                <div class="col">
                                    <select onchange="getSections(this);" class="form-select" aria-label="Class" name="class_id" required>
                                        @isset($school_classes)
                                            <option selected disabled>Please select a class</option>
                                            @foreach ($school_classes as $school_class)
                                                <option value="{{$school_class->id}}" {{($school_class->id == request()->query('class_id'))?'selected="selected"':''}}>
                                                    {{$school_class->class_name}}
                                                </option>
                                            @endforeach
                                        @endisset
                                    </select>
                                </div>
                                <div class="col">
                                    <select class="form-select" id="section-select" aria-label="Section" name="section_id" required>
                                        <option value="{{request()->query('section_id')}}">{{request()->query('section_name')}}</option>
                                    </select>
                                </div>
                                <div class="col">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-arrow-counterclockwise"></i> Load List
                                    </button>
                                </div>
                            </form>

                            {{-- ✅ عرض اسم الشعبة إن وجدت --}}
                            @foreach ($studentList as $student)
                                @if ($loop->first)
                                    <p class="mt-3"><b>Section:</b> {{$student->section->section_name}}</p>
                                    @break
                                @endif
                            @endforeach

                            <div class="bg-white border shadow-sm p-3 mt-4">
                                {{-- ✅ مربع بحث للمعلم أو الأدمن --}}
                                <input type="text" id="searchInput" class="form-control mb-3" placeholder="ابحث بالاسم أو البريد...">

                                <table class="table table-hover table-bordered" id="studentsTable">
                                    <thead class="table-light">
                                    <tr>
                                        <th scope="col">ID Card Number</th>
                                        <th scope="col">Photo</th>
                                        <th scope="col">First Name</th>
                                        <th scope="col">Last Name</th>
                                        <th scope="col">Email</th>
                                        <th scope="col">Phone</th>
                                        <th scope="col">Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach ($studentList as $student)
                                        <tr>
                                            <th scope="row">{{$student->id_card_number}}</th>
                                            <td>
                                                @if (isset($student->student->photo))
                                                    <img src="{{asset('/storage'.$student->student->photo)}}" class="rounded" alt="Profile picture" height="30" width="30">
                                                @else
                                                    <i class="bi bi-person-square"></i>
                                                @endif
                                            </td>
                                            <td class="student-name">{{$student->student->first_name}}</td>
                                            <td>{{$student->student->last_name}}</td>
                                            <td class="student-email">{{$student->student->email}}</td>
                                            <td>{{$student->student->phone}}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{route('student.attendance.show', ['id' => $student->student->id])}}" class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-eye"></i> Attendance
                                                    </a>
                                                    <a href="{{url('students/view/profile/'.$student->student->id)}}" class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-eye"></i> Profile
                                                    </a>
                                                    @can('edit users')
                                                        <a href="{{route('student.edit.show', ['id' => $student->student->id])}}" class="btn btn-sm btn-outline-primary">
                                                            <i class="bi bi-pen"></i> Edit
                                                        </a>
                                                    @endcan
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                @include('layouts.footer')
            </div>
        </div>
    </div>

    <script>
        // 🔎 بحث بسيط بالـ JavaScript بدون إعادة تحميل
        const searchInput = document.getElementById('searchInput');
        const tableBody = document.querySelector('#studentsTable tbody');

        searchInput.addEventListener('keyup', function () {
            const term = this.value.toLowerCase();
            for (let row of tableBody.rows) {
                const name = row.querySelector('.student-name').textContent.toLowerCase();
                const email = row.querySelector('.student-email').textContent.toLowerCase();
                row.style.display = (name.includes(term) || email.includes(term)) ? '' : 'none';
            }
        });

        function getSections(obj) {
            var class_id = obj.options[obj.selectedIndex].value;
            var url = "{{route('get.sections.courses.by.classId')}}?class_id=" + class_id

            fetch(url)
                .then((resp) => resp.json())
                .then(function(data) {
                    var sectionSelect = document.getElementById('section-select');
                    sectionSelect.options.length = 0;
                    data.sections.unshift({'id': 0,'section_name': 'Please select a section'})
                    data.sections.forEach(function(section, key) {
                        sectionSelect[key] = new Option(section.section_name, section.id);
                    });
                })
                .catch(function(error) {
                    console.log(error);
                });
        }
    </script>
@endsection

