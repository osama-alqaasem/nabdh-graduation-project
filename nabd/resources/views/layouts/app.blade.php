<!doctype html>
<html lang="ar" dir="rtl" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'نبض AI - لوحة التحكم')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
<!-- جرب هذا الرابط البديل (Font Awesome 6.5.2) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.2/css/all.min.css">

    <style>
       :root {
    --sidebar-width: 280px;
    --primary-color: #6366f1;
    --bg-main: #f8fafc;
    --card-bg: #ffffff;
    --text-main: #1e293b;
    --sidebar-bg: linear-gradient(180deg, #0f172a 0%, #1e293b 100%);
    --topbar-bg: rgba(255, 255, 255, 0.85);
}

       body {
    font-family: 'Tajawal', sans-serif;
    background-color: var(--bg-main) !important;
    color: var(--text-main);
    transition: background-color 0.3s ease, color 0.3s ease;
}
.card {
    background-color: var(--card-bg) !important;
    border-color: var(--bs-border-color-translucent) !important;
    color: var(--text-main) !important;
}

        /* هيكل التطبيق */
        .app-shell {
            display: flex;
            min-height: 100vh;
            width: 100%;
        }

        /* القائمة الجانبية */
        .sidebar {
            width: var(--sidebar-width);
           background: var(--sidebar-bg) !important;
            color: #fff;
            position: fixed;
            height: 100vh;
            right: 0;
            top: 0;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            transition: all 0.3s ease;
            box-shadow: -5px 0 20px rgba(0,0,0,0.15);
            padding: 1.5rem;
        }

        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 1rem 0.5rem;
            margin-bottom: 2rem;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }

        .brand-icon {
            width: 45px;
            height: 45px;
            background: #fff;
            color: var(--primary-color);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            box-shadow: 0 8px 15px rgba(0,0,0,0.1);
        }

        /* الروابط */
        .nav-link {
            color: rgba(255,255,255,0.7) !important;
            padding: 12px 18px !important;
            border-radius: 16px !important;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            font-weight: 500;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .nav-link i {
            font-size: 1.2rem;
            margin-left: 12px;
            transition: transform 0.3s ease;
        }

        .nav-link:hover {
            background: rgba(255,255,255,0.08);
            color: #fff !important;
            transform: translateX(-5px);
        }

        .nav-link:hover i {
            transform: scale(1.2) rotate(-5deg);
        }

        .nav-link.active {
            background: var(--primary-color) !important;
            color: #fff !important;
            box-shadow: 0 10px 20px rgba(99, 102, 241, 0.3);
        }

        /* المنطقة الرئيسية */
        .main-area {
            margin-right: var(--sidebar-width);
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            width: calc(100% - var(--sidebar-width));
        }

        /* الشريط العلوي */
        .app-topbar {
            height: 80px;
            background: var(--topbar-bg) !important;
            backdrop-filter: blur(15px);
            border-bottom: 1px solid rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 900;
            padding: 0 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        [data-bs-theme="dark"] .app-topbar {
            background: #0f172ad9;
            border-bottom-color: rgba(255,255,255,0.05);
        }

        /* زر الثيم */
        .theme-toggle-btn {
            width: 45px;
            height: 45px;
            border-radius: 12px;
            border: none;
            background: rgb(48, 48, 128);
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: 0.3s;
        }

    [data-bs-theme="dark"] {
    --bg-main: #0f172a;        /* خلفية الصفحة الداكنة */
    --card-bg: #1e293b;        /* خلفية البطاقات */
    --text-main: #f1f5f9;      /* لون النصوص */
    --topbar-bg: rgba(15, 23, 42, 0.85);
    --sidebar-bg: #020617;     /* لون السايدبار في الوضع الليلي */
}

        .theme-toggle-btn:hover {
            transform: translateY(-2px) rotate(15deg);
        }

        /* الملف الشخصي في السايدبار */
        .user-sidebar-card {
            background: rgba(255,255,255,0.05);
            border-radius: 20px;
            padding: 1.2rem;
            margin-top: auto;
            border: 1px solid rgba(255,255,255,0.05);
        }

        .logout-btn {
            background: #ef4444;
            color: #fff;
            border: none;
            border-radius: 12px;
            padding: 10px;
            width: 100%;
            font-weight: bold;
            margin-top: 10px;
            transition: 0.3s;
        }

        .logout-btn:hover {
            background: #dc2626;
            transform: scale(1.02);
        }

        /* محتوى الصفحة */
        .page-content {
            padding: 2rem;
            flex-grow: 1;
        }

        /* تنبيهات */
        .alert {
            border: none;
            border-radius: 18px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.02);
        }

        /* التجاوب للهواتف */
        @media (max-width: 992px) {
            .sidebar { transform: translateX(100%); }
            .main-area { margin-right: 0; width: 100%; }
            .sidebar.show { transform: translateX(0); }
        }
    </style>
</head>

<body>

<div class="app-shell">

    <aside class="sidebar">
        <div class="sidebar-brand">
            <div class="brand-icon">
                <i class="fa-solid fa-heart-pulse"></i>
            </div>
            <div>
                <div class="fw-bold fs-5 mb-0" style="letter-spacing: 0.5px;">نـبـض <span class="text-white-50">AI</span></div>
                <div style="font-size: 0.7rem; color: rgba(255,255,255,0.5)">للتحليل الطبي الذكي</div>
            </div>
        </div>

        <nav class="nav flex-column flex-grow-1">
            <a class="nav-link {{ request()->routeIs('dashboard.index') ? 'active' : '' }}" href="{{ route('dashboard.index') }}">
                <i class="fa-solid fa-house-chimney"></i> الرئيسية
            </a>

            <a class="nav-link {{ request()->routeIs('dashboard.users.*') ? 'active' : '' }}" href="{{ route('dashboard.users.index') }}">
                <i class="fa-solid fa-user-doctor"></i> المستخدمون
            </a>

            <a class="nav-link {{ request()->routeIs('dashboard.patients.*') ? 'active' : '' }}" href="{{ route('dashboard.patients.index') }}">
                <i class="fa-solid fa-hospital-user"></i> سجل المرضى
            </a>

            <a class="nav-link {{ request()->routeIs('dashboard.analyses.*') ? 'active' : '' }}" href="{{ route('dashboard.analyses.index') }}">
                <i class="fa-solid fa-microscope"></i> التحاليل
            </a>

            <a class="nav-link {{ request()->routeIs('dashboard.banners.*') ? 'active' : '' }}" href="{{ route('dashboard.banners.index') }}">
                <i class="fa-solid fa-images"></i> الإعلانات
            </a>

            <a class="nav-link {{ request()->routeIs('dashboard.reports.*') ? 'active' : '' }}" href="{{ route('dashboard.reports.index') }}">
                <i class="fa-solid fa-file-waveform"></i> التقارير
            </a>
        </nav>

        <div class="user-sidebar-card shadow-sm">
            <div class="d-flex align-items-center gap-2 mb-2">
                <div class="rounded-circle bg-white text-dark d-flex align-items-center justify-content-center" style="width:35px; height:35px; font-weight:bold; font-size: 0.8rem">
                    {{ mb_substr(auth()->user()->name ?? 'م', 0, 1) }}
                </div>
                <div class="small fw-bold text-truncate" style="max-width: 150px;">{{ auth()->user()->name ?? 'مستخدم' }}</div>
            </div>
            
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="logout-btn small shadow-sm">
                    <i class="fa-solid fa-power-off me-1"></i> خروج
                </button>
            </form>
        </div>
    </aside>

    <main class="main-area">

        <header class="app-topbar shadow-sm">
            <div class="d-flex align-items-center">
                <button class="btn btn-light d-lg-none me-3" onclick="document.querySelector('.sidebar').classList.toggle('show')">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <div>
                    <h5 class="fw-bold mb-0">@yield('page_title', 'لوحة التحكم')</h5>
                    <div class="text-muted small d-none d-md-block"><i class="fa-solid fa-calendar-day me-1"></i> {{ now()->translatedFormat('l, d F Y') }}</div>
                </div>
            </div>

            <div class="d-flex align-items-center gap-3">
                <button class="theme-toggle-btn shadow-sm" onclick="toggleTheme()" title="تبديل الوضع">
                    <i id="themeIcon" class="fa-solid fa-sun-bright"></i>
                </button>

                <a href="{{ route('dashboard.reports.pdf') }}" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
                    <i class="fa-solid fa-file-pdf ms-1"></i> تصدير PDF
                </a>
            </div>
        </header>

        <div class="page-content">

            @if(session('success'))
                <div class="alert alert-success d-flex align-items-center p-3 mb-4 shadow-sm animate__animated animate__fadeInDown">
                    <i class="fa-solid fa-circle-check fs-4 me-3"></i>
                    <div class="fw-bold">{{ session('success') }}</div>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger p-3 mb-4 shadow-sm">
                    <div class="d-flex align-items-center mb-2">
                        <i class="fa-solid fa-circle-exclamation fs-4 me-3"></i>
                        <strong class="fw-bold">تنبيه! هناك بعض الأخطاء:</strong>
                    </div>
                    <ul class="mb-0 small">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')

        </div>

    </main>

</div>

<script>
// وظيفة تبديل الوضع (Light/Dark)
function toggleTheme() {
    const html = document.documentElement;
    const icon = document.getElementById('themeIcon');
    let current = html.getAttribute('data-bs-theme');

    if (current === 'dark') {
        html.setAttribute('data-bs-theme', 'light');
        icon.className = 'fa-solid fa-sun-bright';
        localStorage.setItem('theme', 'light');
    } else {
        html.setAttribute('data-bs-theme', 'dark');
        icon.className = 'fa-solid fa-moon';
        localStorage.setItem('theme', 'dark');
    }
}

// استعادة الوضع المفضل عند التحميل
document.addEventListener("DOMContentLoaded", function () {
    const saved = localStorage.getItem('theme') || 'light';
    const html = document.documentElement;
    const icon = document.getElementById('themeIcon');

    html.setAttribute('data-bs-theme', saved);
    if (saved === 'dark') {
        icon.className = 'fa-solid fa-moon';
    } else {
        icon.className = 'fa-solid fa-sun-bright';
    }
});
</script>

@stack('scripts')

</body>
</html>