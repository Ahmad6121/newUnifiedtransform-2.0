<!doctype html>
<html lang="{{ str_replace('_','-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'SmartSchool') }}</title>

    <style>
        body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial; background:#f6f7fb; margin:0;}
        .wrap{min-height:100vh; display:flex; align-items:center; justify-content:center; padding:24px;}
        .card{width:100%; max-width:960px; background:#fff; border:1px solid #e8e8ee; border-radius:14px; box-shadow:0 10px 30px rgba(0,0,0,.06); overflow:hidden;}
        .top{padding:28px 28px 18px; border-bottom:1px solid #f0f0f5; display:flex; justify-content:space-between; align-items:center; gap:12px;}
        .brand{display:flex; align-items:center; gap:10px; font-weight:700; font-size:20px;}
        .brand .home{width:38px; height:38px; border-radius:10px; background:#0d6efd12; display:grid; place-items:center; color:#0d6efd;}
        .btns a{display:inline-block; padding:10px 14px; border-radius:10px; text-decoration:none; font-weight:600; border:1px solid transparent;}
        .btn-primary{background:#0d6efd; color:#fff;}
        .btn-light{background:#fff; border-color:#e6e6ee; color:#111;}
        .hero{padding:34px 28px;}
        .hero h1{margin:0 0 8px; font-size:34px;}
        .hero p{margin:0; color:#555; line-height:1.6;}
        .grid{display:grid; grid-template-columns:repeat(3,1fr); gap:14px; margin-top:22px;}
        .box{border:1px solid #f0f0f5; border-radius:12px; padding:14px;}
        .box b{display:block; margin-bottom:6px;}
        .foot{padding:14px 28px; color:#777; border-top:1px solid #f0f0f5; display:flex; justify-content:space-between;}
        @media (max-width: 860px){ .grid{grid-template-columns:1fr;} .top{flex-direction:column; align-items:flex-start;} }
    </style>
</head>
<body>
<div class="wrap">
    <div class="card">
        <div class="top">
            <div class="brand">
                <div class="home">🏫</div>
                <div>{{ config('app.name', 'SmartSchool') }}</div>
            </div>

            <div class="btns">
                @if (Route::has('login'))
                    @auth
                        <a class="btn-primary" href="{{ url('/home') }}">Go to Dashboard</a>
                    @else
                        <a class="btn-primary" href="{{ route('login') }}">Login</a>

                    @endauth
                @endif
            </div>
        </div>

        <div class="hero">
            <h1>Welcome to {{ config('app.name', 'SmartSchool') }} 👋</h1>
            <p>School management made simple — students, teachers, attendance, exams, grades, reports, and more.</p>

            <div class="grid">
                <div class="box"><b>Students & Teachers</b><span style="color:#666">Manage profiles, classes, sections.</span></div>
                <div class="box"><b>Exams & Grades</b><span style="color:#666">Assessments, gradebook, reports.</span></div>
                <div class="box"><b>Notices & Events</b><span style="color:#666">Announcements and calendar scheduling.</span></div>
            </div>
        </div>

        <div class="foot">
            <div>© {{ date('Y') }} {{ config('app.name', 'SmartSchool') }}</div>
            <div>All rights reserved.</div>
        </div>
    </div>
</div>
</body>
</html>
