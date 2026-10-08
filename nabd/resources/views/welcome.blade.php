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

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>

    <style>
        :root {
            --sidebar-width: 290px;
            --primary-grad: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            --sidebar-bg: linear-gradient(180deg, #0f172a 0%, #1e293b 100%);
            --glass-card: rgba(255, 255, 255, 0.9);
            --bg-light: #f8fafc;
        }

        body { font-family: 'Tajawal', sans-serif; background-color: var(--bg-light); margin: 0; padding: 0; overflow-x: hidden; }

        /* Sidebar Glassmorphism */
        .sidebar {
            width: var(--sidebar-width); height: 100vh; position: fixed; right: 0; top: 0;
            background: var(--sidebar-bg); z-index: 1050; padding: 1.8rem; display: flex; flex-direction: column;
            box-shadow: -10px 0 30px rgba(0,0,0,0.2); transition: 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .sidebar-brand { display: flex; align-items: center; gap: 15px; padding-bottom: 2rem; border-bottom: 1px solid rgba(255,255,255,0.08); margin-bottom: 2rem; }
        .logo-box { width: 50px; height: 50px; background: #fff; color: #6366f1; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; box-shadow: 0 8px 15px rgba(99,102,241,0.3); }

        .nav-link {
            color: rgba(255,255,255,0.7) !important; padding: 14px 20px !important; border-radius: 20px !important; margin-bottom: 10px;
            display: flex; align-items: center; justify-content: space-between; transition: all 0.3s ease; font-weight: 500;
        }

        .nav-link i { font-size: 1.2rem; margin-left: 12px; transition: 0.3s; }
        .nav-link:hover { background: rgba(255,255,255,0.05); color: #fff !important; transform: translateX(-8px); }
        .nav-link.active { background: var(--primary-grad) !important; color: #fff !important; box-shadow: 0 10px 25px rgba(99,102,241,0.4); }

        /* Stickers/Icons in Sidebar */
        .nav-sticker { width: 32px; height: 32px; background: rgba(255,255,255,0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1rem; }

        /* Main Content */
        .main-content { margin-right: var(--sidebar-width); width: calc(100% - var(--sidebar-width)); min-height: 100vh; }
        .top-nav { height: 85px; background: rgba(255,255,255,0.85); backdrop-filter: blur(15px); border-bottom: 1px solid rgba(0,0,0,0.05); position: sticky; top: 0; z-index: 1000; padding: 0 2.5rem; display: flex; align-items: center; justify-content: space-between; }

        /* Glass Cards */
        .glass-card { background: var(--glass-card); border-radius: 30px !important; border: 1px solid rgba(255,255,255,0.4) !important; box-shadow: 0 15px 45px rgba(0,0,0,0.04) !important; transition: 0.3s; }
        .hover-card:hover { transform: translateY(-8px); box-shadow: 0 20px 50px rgba(0,0,0,0.08) !important; }

        .btn-round { border-radius: 18px; padding: 12px 25px; font-weight: 700; transition: 0.3s; }

        /* Fix Chart Overflow */
        .chart-container { position: relative; height: 350px; width: 100%; }

        @media (max-width: 992px) {
            .sidebar { transform: translateX(100%); } .main-content { margin-right: 0; width: 100%; }
            .sidebar.show { transform: translateX(0); }
        }
    </style>
</head>
<body>

<div class="app-shell d-flex">
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <div class="logo-box"><i class="fa-solid fa-heart-pulse"></i></div>
            <div class="text-white">
                <h4 class="fw-bold mb-0">نـبـض AI</h4>
                <small class="opacity-50">التشخيص الذكي</small>
            </div>
        </div>

        <nav class="nav flex-column flex-grow-1">
            <a class="nav-link {{ request()->routeIs('dashboard.index') ? 'active' : '' }}" href="{{ route('dashboard.index') }}">
                <span><i class="fa-solid fa-house-user"></i> الرئيسية</span>
                <span class="nav-sticker">🏠</span>
            </a>
            <a class="nav-link {{ request()->routeIs('dashboard.patients.*') ? 'active' : '' }}" href="{{ route('dashboard.patients.index') }}">
                <span><i class="fa-solid fa-user-injured"></i> المرضى</span>
                <span class="nav-sticker">👥</span>
            </a>
            <a class="nav-link {{ request()->routeIs('dashboard.analyses.*') ? 'active' : '' }}" href="{{ route('dashboard.analyses.index') }}">
                <span><i class="fa-solid fa-microscope"></i> التحاليل</span>
                <span class="nav-sticker">🧪</span>
            </a>
            <a class="nav-link {{ request()->routeIs('dashboard.banners.*') ? 'active' : '' }}" href="{{ route('dashboard.banners.index') }}">
                <span><i class="fa-solid fa-clapperboard"></i> الإعلانات</span>
                <span class="nav-sticker">🖼️</span>
            </a>
            <a class="nav-link {{ request()->routeIs('dashboard.reports.*') ? 'active' : '' }}" href="{{ route('dashboard.reports.index') }}">
                <span><i class="fa-solid fa-file-waveform"></i> التقارير</span>
                <span class="nav-sticker">📊</span>
            </a>
        </nav>

        <div class="mt-auto user-card p-3 rounded-4 bg-white bg-opacity-5">
            <div class="d-flex align-items-center gap-2 mb-3">
                <div class="rounded-circle bg-white text-dark d-flex align-items-center justify-content-center fw-bold" style="width:35px; height:35px">
                    {{ mb_substr(auth()->user()->name ?? 'م', 0, 1) }}
                </div>
                <div class="text-white small fw-bold text-truncate" style="max-width: 150px">{{ auth()->user()->name }}</div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="btn btn-danger btn-sm w-100 rounded-pill fw-bold">تسجيل خروج</button>
            </form>
        </div>
    </aside>

    <main class="main-content">
        <header class="top-nav shadow-sm">
            <div class="d-flex align-items-center">
                <button class="btn btn-light d-lg-none me-3" onclick="document.getElementById('sidebar').classList.toggle('show')">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <h5 class="fw-bold mb-0">@yield('page_title')</h5>
            </div>
            <div class="d-flex gap-2">
                <div class="bg-light border p-2 rounded-4 px-3 small fw-bold">
                    <i class="fa-solid fa-calendar-check text-primary me-2"></i> {{ now()->format('Y-m-d') }}
                </div>
                <a href="{{ route('dashboard.reports.pdf') }}" class="btn btn-primary rounded-pill px-4 fw-bold">
                    تصدير PDF <i class="fa-solid fa-file-export ms-2"></i>
                </a>
            </div>
        </header>

        <div class="p-4 p-lg-5">
            @if(session('success'))
                <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4 p-3 fw-bold animate__animated animate__fadeIn">
                    <i class="fa-solid fa-check-double me-2"></i> {{ session('success') }}
                </div>
            @endif
            @yield('content')
        </div>
    </main>
</div>

@stack('scripts')
</body>
</html>

### 2. واجهة الداشبورد (Dashboard) - `index.blade.php`
مع حل مشكلة الرسم البياني الذي "يكبر بلا توقف".

```html
@extends('layouts.app')
@section('page_title', 'نظرة عامة على النظام')

@section('content')
<style>
    .ai-card { color: white; border: none; overflow: hidden; position: relative; }
    .ai-card .big-icon { position: absolute; left: -15px; bottom: -15px; font-size: 6rem; opacity: 0.15; }
    .bg-safe { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
    .bg-caution { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
    .bg-danger { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); }
    .bg-primary-grad { background: var(--primary-grad); }
</style>

<div class="row g-4 mb-5">
    @foreach($resultCards as $card)
        <div class="col-md-6 col-xl-3">
            @php
                $bg = 'bg-primary-grad'; $icon = 'fa-heart-circle-check';
                if($card['label'] === 'طبيعي') { $bg = 'bg-safe'; $icon = 'fa-check-circle'; }
                elseif($card['label'] === 'اضطراب بنبض القلب') { $bg = 'bg-caution'; $icon = 'fa-wave-square'; }
                elseif($card['label'] === 'مصاب بجلطة') { $bg = 'bg-danger'; $icon = 'fa-biohazard'; }
            @endphp
            <div class="card glass-card ai-card h-100 hover-card {{ $bg }}">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 opacity-75 fw-bold">{{ $card['label'] }}</p>
                            <h2 class="fw-extrabold mb-0">{{ $card['count'] }}</h2>
                        </div>
                        <i class="fa-solid {{ $icon }} fs-1"></i>
                    </div>
                </div>
                <i class="fa-solid {{ $icon }} big-icon"></i>
            </div>
        </div>
    @endforeach
</div>

<div class="row g-4 mb-5">
    <div class="col-lg-8">
        <div class="card glass-card h-100 p-4">
            <h5 class="fw-bold mb-4">📊 توزيع نتائج التشخيص الذكي</h5>
            <div class="chart-container">
                <canvas id="resultsChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card glass-card h-100 p-4">
            <h5 class="fw-bold mb-4">📍 المؤشرات السريعة</h5>
            <div class="grid gap-3">
                <div class="p-3 rounded-4 bg-light border-start border-4 border-primary">
                    <small class="text-muted d-block">إجمالي المستخدمين</small>
                    <h4 class="fw-bold mb-0">{{ $usersCount }} 👤</h4>
                </div>
                <div class="p-3 rounded-4 bg-light border-start border-4 border-success mt-3">
                    <small class="text-muted d-block">المرضى المسجلين</small>
                    <h4 class="fw-bold mb-0">{{ $patientsCount }} 🏥</h4>
                </div>
                <div class="p-3 rounded-4 bg-light border-start border-4 border-warning mt-3">
                    <small class="text-muted d-block">إعلانات نشطة</small>
                    <h4 class="fw-bold mb-0">{{ $activeBannersCount }} 📢</h4>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card glass-card h-100 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold mb-0">✨ المرضى الجدد</h5>
                <a href="{{ route('dashboard.patients.index') }}" class="btn btn-sm btn-light border rounded-pill">الكل</a>
            </div>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead class="table-light">
                        <tr><th class="rounded-start-4">المريض</th><th>المسؤول</th><th class="rounded-end-4">الملف</th></tr>
                    </thead>
                    <tbody>
                        @foreach($recentPatients as $patient)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width:35px; height:35px">
                                        {{ mb_substr($patient->full_name, 0, 1) }}
                                    </div>
                                    <span class="fw-bold">{{ $patient->full_name }}</span>
                                </div>
                            </td>
                            <td>{{ $patient->user?->name }}</td>
                            <td><a href="{{ route('dashboard.patients.show', $patient) }}" class="btn btn-sm btn-outline-primary rounded-pill">عرض</a></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card glass-card h-100 p-4">
            <h5 class="fw-bold mb-4">🖼️ أحدث الإعلانات</h5>
            <div class="vstack gap-3">
                @forelse($latestBanners as $banner)
                <div class="d-flex gap-3 align-items-center p-2 hover-bg-light rounded-4">
                    <img src="{{ asset('storage/' . $banner->image_path) }}" class="rounded-4 border" style="width:70px; height:60px; object-fit:cover">
                    <div class="overflow-hidden">
                        <div class="fw-bold text-truncate">{{ $banner->title }}</div>
                        <small class="text-muted text-truncate d-block">{{ $banner->description }}</small>
                    </div>
                </div>
                @empty
                    <div class="text-center py-5 text-muted small">لا توجد إعلانات</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('resultsChart');
    if(ctx){
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: @json($resultCards->pluck('label')),
                datasets: [{
                    label: 'عدد الحالات',
                    data: @json($resultCards->pluck('count')),
                    backgroundColor: ['#10b981', '#f59e0b', '#ef4444', '#6366f1'],
                    borderRadius: 15,
                    barThickness: 45
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false, // الحل النهائي لمشكلة تمدد الرسم
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#f1f1f1' }, border: { display: false } },
                    x: { grid: { display: false }, border: { display: false } }
                }
            }
        });
    }
});
</script>
@endpush

### 3. صفحة تفاصيل المريض (Show Patient)
تم تحويلها إلى ملف "Profile" عصري مع أيقونات الحالة الصحية.

```html
@extends('layouts.app')
@section('page_title', 'الملف الطبي للمريض')

@section('content')
<div class="row g-4">
    <div class="col-lg-4">
        <div class="card glass-card sticky-top" style="top: 100px">
            <div class="card-body p-4 text-center">
                <div class="mx-auto mb-4 bg-primary bg-opacity-10 text-primary rounded-4 d-flex align-items-center justify-content-center shadow-sm" style="width:100px; height:100px; font-size: 3rem; font-weight: 800">
                    {{ mb_substr($patient->full_name, 0, 1) }}
                </div>
                <h3 class="fw-bold mb-1">{{ $patient->full_name }}</h3>
                <p class="text-muted small mb-4">المسؤول الطبي: {{ $patient->user?->name }} 👨‍⚕️</p>
                <hr class="opacity-10">
                <div class="row text-start g-3">
                    <div class="col-6">
                        <small class="text-muted d-block">الجنس</small>
                        <span class="fw-bold">{{ $patient->gender === 'male' ? 'ذكر ♂️' : 'أنثى ♀️' }}</span>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">العمر</small>
                        <span class="fw-bold">{{ $patient->age }} سنة 🎂</span>
                    </div>
                    <div class="col-12 mt-4">
                        <div class="p-3 rounded-4 bg-primary bg-opacity-5 border border-primary border-opacity-10">
                            <small class="text-muted d-block">إجمالي التحاليل</small>
                            <h4 class="fw-bold text-primary mb-0">{{ $patient->analyses->count() }} فحص 🧪</h4>
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2 mt-4