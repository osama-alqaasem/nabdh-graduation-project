@extends('layouts.app')

@section('title', 'لوحة المعلومات الذكية')

@section('content')
<style>
    /* التنسيقات العصرية */
    :root {
        --glass-bg: rgba(255, 255, 255, 0.7);
        --glass-border: rgba(255, 255, 255, 0.3);
        --primary-gradient: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
    }

    body { background-color: #f3f4f6; font-family: 'Tajawal', sans-serif; }

    /* تأثيرات الكروت */
    .glass-card {
        background: var(--glass-bg);
        backdrop-filter: blur(10px);
        border: 1px solid var(--glass-border);
        border-radius: 20px !important;
        box-shadow: 0 8px 32px rgba(31, 38, 135, 0.07);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .hover-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.1) !important;
    }

    /* ألوان كروت النتائج المخصصة */
    .metric-positive { background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; } /* طبيعي */
    .metric-warning { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white; } /* اضطراب */
    .metric-negative { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: white; } /* جلطة */
    .metric-banners { background: linear-gradient(135deg, #6366f1 0%, #4338ca 100%); color: white; } /* أخرى */

    .metric-card .opacity-50 { font-size: 3rem; position: absolute; left: 15px; bottom: 10px; }
    .metric-card { position: relative; overflow: hidden; border: none !important; }

    /* أيقونة الأفاتار */
    .avatar-circle {
        width: 45px; height: 45px; background: var(--primary-gradient);
        color: white; display: flex; align-items: center; justify-content: center;
        border-radius: 12px; font-weight: bold; font-size: 1.2rem;
    }

    .soft-badge {
        background: rgba(99, 102, 241, 0.1); color: #4f46e5;
        padding: 6px 15px; border-radius: 30px; font-weight: bold; font-size: 0.85rem;
    }

    /* حاوية الرسم البياني - حل مشكلة التمدد */
    .chart-container { position: relative; height: 300px; width: 100%; }

    .btn-action { border-radius: 12px; padding: 5px 15px; transition: 0.3s; }
</style>

<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h1 class="fw-bold text-dark mb-1">مرحبًا {{ auth()->user()->name }} 👋</h1>
            <p class="text-muted">إليك ملخص شامل لنظام التحليل الذكي اليوم.</p>
        </div>
        <div class="soft-badge shadow-sm">
            <i class="fa-solid fa-calendar-day me-2"></i> {{ now()->translatedFormat('l، j F Y') }}
        </div>
    </div>

    <div class="row g-3 mb-4">
        @foreach($resultCards as $card)
            <div class="col-md-6 col-xl-3">
                @php
                    $bgClass = ''; $icon = 'fa-heart-pulse';
                    if($card['label'] === 'طبيعي') { $bgClass = 'metric-positive'; $icon = 'fa-circle-check'; }
                    elseif($card['label'] === 'اضطراب بنبض القلب') { $bgClass = 'metric-warning'; $icon = 'fa-wave-square'; }
                    elseif($card['label'] === 'مصاب بجلطة') { $bgClass = 'metric-negative'; $icon = 'fa-biohazard'; }
                    else { $bgClass = 'metric-banners'; $icon = 'fa-chart-pie'; }
                @endphp
                <div class="card metric-card h-100 hover-card glass-card {{ $bgClass }}">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="small opacity-75 fw-bold">{{ $card['label'] }}</div>
                                <div class="fs-2 fw-bold mt-1">{{ $card['count'] }}</div>
                            </div>
                            <i class="fa-solid {{ $icon }} opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6 col-xl-4">
            <div class="card glass-card h-100 hover-card border-0">
                <div class="card-body d-flex align-items-center p-4">
                    <div class="rounded-circle p-3 bg-primary bg-opacity-10 me-3">
                        <i class="fa-solid fa-users fs-4 text-primary"></i>
                    </div>
                    <div>
                        <div class="small text-muted fw-bold">إجمالي المستخدمين</div>
                        <div class="fs-3 fw-bold">{{ $usersCount }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-4">
            <div class="card glass-card h-100 hover-card border-0">
                <div class="card-body d-flex align-items-center p-4">
                    <div class="rounded-circle p-3 bg-success bg-opacity-10 me-3">
                        <i class="fa-solid fa-user-injured fs-4 text-success"></i>
                    </div>
                    <div>
                        <div class="small text-muted fw-bold">المرضى المسجلين</div>
                        <div class="fs-3 fw-bold">{{ $patientsCount }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-4">
            <div class="card glass-card h-100 hover-card border-0">
                <div class="card-body d-flex align-items-center p-4">
                    <div class="rounded-circle p-3 bg-warning bg-opacity-10 me-3">
                        <i class="fa-solid fa-rectangle-ad fs-4 text-warning"></i>
                    </div>
                    <div>
                        <div class="small text-muted fw-bold">إعلانات نشطة</div>
                        <div class="fs-3 fw-bold">{{ $activeBannersCount }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card glass-card h-100 p-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold mb-0">توزيع نتائج التشخيص الذكي</h5>
                        <span class="badge bg-light text-dark border">إحصائيات فورية</span>
                    </div>
                    <div class="chart-container">
                        <canvas id="resultsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card glass-card h-100 p-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold mb-0">أحدث الإعلانات</h5>
                        <a href="{{ route('dashboard.banners.index') }}" class="btn btn-sm btn-link text-decoration-none">إدارة</a>
                    </div>
                    <div class="vstack gap-3">
                        @forelse($latestBanners as $banner)
                            <div class="d-flex gap-3 align-items-center p-2 rounded-4 hover-bg-light transition">
                                <img src="{{ asset('storage/' . $banner->image_path) }}" class="rounded-3 shadow-sm" style="width:65px;height:55px;object-fit:cover;" alt="">
                                <div class="flex-grow-1 overflow-hidden">
                                    <div class="fw-bold text-truncate small">{{ $banner->title }}</div>
                                    <div class="text-muted extra-small text-truncate" style="font-size: 0.75rem;">{{ $banner->description }}</div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-5 text-muted">لا توجد إعلانات حالياً</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card glass-card h-100 p-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold mb-0">قائمة المرضى الجدد</h5>
                        <a href="{{ route('dashboard.patients.index') }}" class="btn btn-sm btn-outline-primary btn-action">عرض الكل</a>
                    </div>
                    <div class="vstack gap-3">
                        @forelse($recentPatients as $patient)
                            <div class="d-flex align-items-center gap-3 p-2 rounded-4 hover-bg-light transition border-bottom border-light">
                                <div class="avatar-circle shadow-sm">{{ mb_substr($patient->full_name, 0, 1) }}</div>
                                <div class="flex-grow-1">
                                    <div class="fw-bold small">{{ $patient->full_name }}</div>
                                    <div class="text-muted extra-small">{{ $patient->user?->name }} • عمر: {{ $patient->age }}</div>
                                </div>
                                <a href="{{ route('dashboard.patients.show', $patient) }}" class="btn btn-sm btn-primary btn-action shadow-sm">الملف</a>
                            </div>
                        @empty
                            <div class="text-center py-5 text-muted">لا يوجد مرضى مسجلين مؤخراً</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card glass-card h-100 p-3">
                <div class="card-body">
                    <h5 class="fw-bold mb-4">المؤشرات السريعة</h5>
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="p-3 rounded-4 border bg-white bg-opacity-50 text-center">
                                <div class="text-muted small fw-bold mb-1">التحاليل</div>
                                <div class="fs-4 fw-bold text-primary">{{ $analysesCount }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 rounded-4 border bg-white bg-opacity-50 text-center">
                                <div class="text-muted small fw-bold mb-1">الأعضاء</div>
                                <div class="fs-4 fw-bold text-dark">{{ $usersCount }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 rounded-4 border bg-white bg-opacity-50 text-center">
                                <div class="text-muted small fw-bold mb-1">الحالات</div>
                                <div class="fs-4 fw-bold text-success">{{ $patientsCount }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 rounded-4 border bg-white bg-opacity-50 text-center">
                                <div class="text-muted small fw-bold mb-1">النشاط</div>
                                <div class="fs-4 fw-bold text-warning">98%</div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 p-3 bg-primary bg-opacity-10 rounded-4 text-primary">
                        <div class="small fw-bold"><i class="fa-solid fa-circle-info me-2"></i> نصيحة النظام</div>
                        <div class="extra-small mt-1">تأكد من مراجعة الحالات التي تظهر تحت تصنيف "مصاب بجلطة" بشكل فوري.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('resultsChart');
    if (ctx && window.Chart) {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: @json($resultCards->pluck('label')),
                datasets: [{
                    label: 'العدد الإجمالي',
                    data: @json($resultCards->pluck('count')),
                    backgroundColor: [
                        '#10b981', // طبيعي
                        '#f59e0b', // اضطراب
                        '#ef4444', // جلطة
                        '#6366f1'  // أخرى
                    ],
                    borderRadius: 10,
                    barThickness: 40
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false, // هذا السطر يمنع الرسم من التضخم اللانهائي
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0 },
                        grid: { display: true, color: 'rgba(0,0,0,0.05)' }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    }
});
</script>
@endpush