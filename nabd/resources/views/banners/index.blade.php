@extends('layouts.app')

@section('title', 'إدارة الإعلانات | نبض AI')
@section('page_title', 'لوحة التحكم بالإعلانات')

@section('content')
<style>
    /* تنسيقات حصرية لبطاقات الإعلانات */
    .banner-container {
        animation: fadeIn 0.8s ease-out;
    }

    .banner-card {
        border: none;
        border-radius: 30px;
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(15px);
        overflow: hidden;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03);
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .banner-card:hover {
        transform: translateY(-12px);
        box-shadow: 0 20px 50px rgba(99, 102, 241, 0.1);
    }

    .banner-img-wrapper {
        position: relative;
        height: 220px;
        overflow: hidden;
    }

    .banner-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.6s ease;
    }

    .banner-card:hover .banner-img {
        transform: scale(1.1);
    }

    .status-floating-badge {
        position: absolute;
        top: 15px;
        left: 15px;
        padding: 6px 15px;
        border-radius: 50px;
        font-weight: 800;
        font-size: 0.75rem;
        backdrop-filter: blur(10px);
        z-index: 2;
    }

    .banner-body {
        padding: 25px;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }

    .banner-title {
        font-weight: 800;
        color: #1e293b;
        margin-bottom: 12px;
        font-size: 1.15rem;
    }

    .banner-desc {
        color: #64748b;
        font-size: 0.9rem;
        line-height: 1.6;
        margin-bottom: 20px;
        flex-grow: 1;
    }

    .search-glass {
        background: rgba(255, 255, 255, 0.7);
        border: 1px solid rgba(255, 255, 255, 0.4);
        border-radius: 20px;
        padding: 25px;
        box-shadow: 0 8px 30px rgba(0,0,0,0.02);
    }

    .btn-action-banner {
        border-radius: 12px;
        padding: 8px 20px;
        font-weight: 700;
        font-size: 0.85rem;
        transition: 0.3s;
    }

    .flutter-sticker {
        background: #e0f2fe;
        color: #0369a1;
        padding: 4px 12px;
        border-radius: 10px;
        font-size: 0.7rem;
        font-weight: 800;
    }
</style>

<div class="banner-container">
    <div class="d-flex justify-content-between align-items-center mb-5 gap-3 flex-wrap">
        <div>
            <h2 class="fw-extrabold text-dark mb-1">لوحة الإعلانات الذكية 🖼️</h2>
            <p class="text-muted mb-0 small">إدارة المحتوى المرئي الذي يظهر للمستخدمين في تطبيق الموبايل</p>
        </div>
        <a href="{{ route('dashboard.banners.create') }}" class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold">
            <i class="fa-solid fa-plus-circle ms-2"></i> إضافة إعلان جديد
        </a>
    </div>

    <div class="card search-glass mb-5">
        <form class="row g-3" method="GET">
            <div class="col-md-10">
                <div class="position-relative">
                    <i class="fa-solid fa-magnifying-glass position-absolute" style="right: 15px; top: 50%; transform: translateY(-50%); color: #94a3b8;"></i>
                    <input type="search" name="q" value="{{ $q }}" class="form-control form-control-lg border-0 shadow-none" style="padding-right: 45px; border-radius: 15px; background: #f8fafc;" placeholder="ابحث عن إعلان معين بواسطة العنوان...">
                </div>
            </div>
            <div class="col-md-2">
                <button class="btn btn-dark btn-lg w-100 rounded-4 fw-bold shadow-sm">تصفية</button>
            </div>
        </form>
    </div>

    <div class="row g-4">
        @forelse($banners as $banner)
        <div class="col-md-6 col-xl-4 animate__animated animate__fadeInUp">
            <div class="banner-card">
                <div class="banner-img-wrapper">
                    @if($banner->is_active)
                        <span class="status-floating-badge bg-success bg-opacity-75 text-white">
                            <i class="fa-solid fa-circle-check me-1"></i> نشط الآن
                        </span>
                    @else
                        <span class="status-floating-badge bg-secondary bg-opacity-75 text-white">
                            <i class="fa-solid fa-eye-slash me-1"></i> مخفي
                        </span>
                    @endif
                    <img src="{{ asset('storage/' . $banner->image_path) }}" class="banner-img" alt="{{ $banner->title }}">
                </div>
                <div class="banner-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h5 class="banner-title text-truncate">{{ $banner->title }}</h5>
                        <span class="flutter-sticker">Flutter API</span>
                    </div>
                    <p class="banner-desc">
                        {{ \Illuminate\Support\Str::limit($banner->description, 110) }}
                    </p>
                    <div class="d-flex gap-2 mt-auto pt-3 border-top border-light">
                        <a href="{{ route('dashboard.banners.edit', $banner) }}" class="btn btn-outline-secondary btn-action-banner flex-grow-1">
                            <i class="fa-solid fa-pen-to-square me-1"></i> تعديل
                        </a>
                        <form action="{{ route('dashboard.banners.destroy', $banner) }}" method="POST" class="flex-grow-1" onsubmit="return confirm('هل أنت متأكد من حذف هذا الإعلان نهائياً؟')">
                            @csrf 
                            @method('DELETE')
                            <button class="btn btn-outline-danger btn-action-banner w-100">
                                <i class="fa-solid fa-trash-can me-1"></i> حذف
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="card glass-card border-0 py-5">
                <div class="card-body text-center">
                    <div class="mb-4">
                        <i class="fa-solid fa-images fs-1 text-muted opacity-25"></i>
                    </div>
                    <h5 class="text-muted fw-bold">لا يوجد أي إعلانات مسجلة في النظام حالياً</h5>
                    <p class="small text-muted">ابدأ بإضافة أول إعلان ليظهر في واجهة تطبيق نبض AI</p>
                    <a href="{{ route('dashboard.banners.create') }}" class="btn btn-primary mt-3 rounded-pill px-4">أضف إعلانك الأول</a>
                </div>
            </div>
        </div>
        @endforelse
    </div>

    <div class="mt-5 d-flex justify-content-center">
        {{ $banners->links() }}
    </div>
</div>
@endsection