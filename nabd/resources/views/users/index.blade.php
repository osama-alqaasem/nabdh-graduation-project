@extends('layouts.app')

@section('title', 'المستخدمون | نبض AI')
@section('page_title', 'إدارة المستخدمون')

@section('content')
<style>
    /* تنسيقات حصرية لصفحة المستخدمين */
    .user-filter-card {
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(10px);
        border-radius: 25px !important;
        border: 1px solid rgba(255, 255, 255, 0.4);
        box-shadow: 0 8px 30px rgba(0,0,0,0.03);
    }

    .user-table-card {
        border-radius: 30px !important;
        border: none;
        box-shadow: 0 15px 50px rgba(0,0,0,0.05);
        overflow: hidden;
    }

    .modern-user-table thead th {
        background-color: #f8fafc;
        color: #64748b;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.85rem;
        padding: 20px;
        border: none;
    }

    .modern-user-table tbody td {
        padding: 18px 20px;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        transition: 0.3s;
    }

    .modern-user-table tbody tr:hover {
        background-color: rgba(99, 102, 241, 0.02);
    }

    .user-avatar-gradient {
        width: 45px;
        height: 45px;
        border-radius: 14px;
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 1.1rem;
        box-shadow: 0 5px 15px rgba(99, 102, 241, 0.2);
    }

    .count-badge {
        padding: 6px 12px;
        border-radius: 12px;
        font-weight: 700;
        font-size: 0.85rem;
        background: rgba(99, 102, 241, 0.1);
        color: #6366f1;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .btn-action-custom {
        width: 38px;
        height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        transition: 0.3s;
        border: none;
    }

    .btn-action-custom:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 12px rgba(0,0,0,0.1);
    }

    .search-container {
        position: relative;
    }

    .search-container i {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
    }

    .search-container input {
        padding-right: 45px !important;
        border-radius: 15px !important;
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-5 gap-3 flex-wrap animate__animated animate__fadeIn">
    <div>
        <h2 class="fw-bold text-dark mb-1">المستخدمون</h2>
        <p class="text-muted mb-0 small">إدارة صلاحيات المستخدمين في نظام نبض AI</p>
    </div>
    <a href="{{ route('dashboard.users.create') }}" class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold">
        <i class="fa-solid fa-user-plus ms-2"></i> إضافة مستخدم جديد
    </a>
</div>

<div class="card user-filter-card mb-4">
    <div class="card-body p-4">
        <form class="row g-3" method="GET">
            <div class="col-md-10">
                <div class="search-container">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="search" name="q" value="{{ $q }}" class="form-control form-control-lg border-light shadow-none" placeholder="ابحث باسم المستخدم أو البريد الإلكتروني المعتمد...">
                </div>
            </div>
            <div class="col-md-2">
                <button class="btn btn-dark btn-lg w-100 rounded-4 fw-bold shadow-sm">
                    بحث سريع
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card user-table-card animate__animated animate__fadeInUp">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table modern-user-table align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">المستخدم المسؤول</th>
                        <th>البريد الإلكتروني</th>
                        <th class="text-center">إجمالي المرضى</th>
                        <th class="text-center">التحاليل المنجزة</th>
                        <th class="text-center">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="user-avatar-gradient">
                                    {{ mb_substr($user->name, 0, 1) }}
                                </div>
                                <div>
                                    <div class="fw-bold text-dark">{{ $user->name }}</div>
                                    <!-- <div class="extra-small text-muted">ID: #USR-{{ $user->id }}</div> -->
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="text-muted small"><i class="fa-solid fa-envelope-open me-1"></i> {{ $user->email }}</span>
                        </td>
                        <td class="text-center">
                            <span class="count-badge">
                                <i class="fa-solid fa-users-medical"></i> {{ $user->patients_count }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="count-badge" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
                                <i class="fa-solid fa-microscope"></i> {{ $user->analyses_count }}
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="d-flex gap-2 justify-content-center">
                                <a href="{{ route('dashboard.users.show', $user) }}" class="btn-action-custom bg-primary bg-opacity-10 text-primary" title="عرض التفاصيل">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <a href="{{ route('dashboard.users.edit', $user) }}" class="btn-action-custom bg-secondary bg-opacity-10 text-secondary" title="تعديل">
                                    <i class="fa-solid fa-user-pen"></i>
                                </a>
                                <form action="{{ route('dashboard.users.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا المستخدم؟ سيؤدي ذلك لتعطيل وصوله للنظام.')">
                                    @csrf 
                                    @method('DELETE')
                                    <button class="btn-action-custom bg-danger bg-opacity-10 text-danger" title="حذف">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fa-solid fa-user-slash fs-1 d-block mb-3 opacity-20"></i>
                                لا توجد نتائج مطابقة لبحثك حالياً
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-4 d-flex justify-content-center">
    {{ $users->links() }}
</div>
@endsection