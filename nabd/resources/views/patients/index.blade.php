@extends('layouts.app')

@section('title', 'سجل المرضى | نبض AI')
@section('page_title', 'إدارة سجلات المرضى')

@section('content')
<style>
    /* تنسيقات مخصصة لجدول المرضى */
    .filter-card {
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(10px);
        border-radius: 25px !important;
        border: 1px solid rgba(255, 255, 255, 0.4);
        box-shadow: 0 8px 30px rgba(0,0,0,0.03);
    }

    .table-card {
        border-radius: 30px !important;
        border: none;
        box-shadow: 0 10px 40px rgba(0,0,0,0.04);
        overflow: hidden;
    }

    .patient-table thead th {
        background-color: #f8fafc;
        color: #64748b;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.5px;
        padding: 20px;
        border: none;
    }

    .patient-table tbody td {
        padding: 20px;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
    }

    .patient-avatar {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        box-shadow: 0 5px 15px rgba(99, 102, 241, 0.2);
    }

    .analysis-count-badge {
        background: rgba(99, 102, 241, 0.1);
        color: #6366f1;
        padding: 6px 14px;
        border-radius: 10px;
        font-weight: 700;
        font-size: 0.85rem;
    }

    .btn-action {
        width: 35px;
        height: 35px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        transition: all 0.3s ease;
    }

    .btn-action:hover {
        transform: translateY(-3px);
    }

    .gender-icon {
        width: 28px;
        height: 28px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        font-size: 0.8rem;
        margin-left: 8px;
    }

    .search-input-group {
        position: relative;
    }

    .search-input-group i {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
    }

    .search-input-group input {
        padding-right: 45px !important;
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-5 gap-3 flex-wrap">
    <div>
        <h2 class="fw-bold text-dark mb-1">سجل المرضى</h2>
        <p class="text-muted mb-0 small">إدارة الملفات الطبية ومتابعة نتائج التحاليل الذكية</p>
    </div>
    <a href="{{ route('dashboard.patients.create') }}" class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold">
        <i class="fa-solid fa-user-plus ms-2"></i> إضافة مريض جديد
    </a>
</div>

<div class="card filter-card mb-4">
    <div class="card-body p-4">
        <form class="row g-3 align-items-end" method="GET">
            <div class="col-md-5">
                <label class="form-label small fw-bold text-muted">البحث عن مريض</label>
                <div class="search-input-group">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="search" name="q" value="{{ $q }}" class="form-control form-control-lg rounded-4 border-light shadow-none" placeholder="اكتب اسم المريض هنا...">
                </div>
            </div>
            <div class="col-md-5">
                <label class="form-label small fw-bold text-muted">تصفية حسب المسؤول</label>
                <select class="form-select form-select-lg rounded-4 border-light shadow-none" name="user_id">
                    <option value="">جميع المستخدمين</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" @selected($userId == $user->id)>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-dark btn-lg w-100 rounded-4 fw-bold shadow-sm">
                    تطبيق الفلترة
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card table-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table patient-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>بيانات المريض</th>
                        <th>المستخدم المسؤول</th>
                        <th>العمر</th>
                        <th>الجنس</th>
                        <th class="text-center">إجمالي التحاليل</th>
                        <th class="text-center">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($patients as $patient)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <div class="patient-avatar">
                                    {{ mb_substr($patient->full_name, 0, 1) }}
                                </div>
                                <div>
                                    <div class="fw-bold text-dark">{{ $patient->full_name }}</div>
                                    <!-- <div class="extra-small text-muted">ID: #PAT-{{ $patient->id }}</div> -->
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="small text-muted"><i class="fa-solid fa-user-doctor me-1 text-primary"></i> {{ $patient->user?->name ?? '-' }}</span>
                        </td>
                        <td>
                            <span class="fw-bold">{{ $patient->age }} سنة</span>
                        </td>
                        <td>
                            @if($patient->gender === 'male')
                                <div class="d-flex align-items-center">
                                    <span class="gender-icon bg-primary bg-opacity-10 text-primary"><i class="fa-solid fa-mars"></i></span>
                                    <span class="small">ذكر</span>
                                </div>
                            @elseif($patient->gender === 'female')
                                <div class="d-flex align-items-center">
                                    <span class="gender-icon bg-danger bg-opacity-10 text-danger"><i class="fa-solid fa-venus"></i></span>
                                    <span class="small">أنثى</span>
                                </div>
                            @else
                                <span class="small text-muted">غير محدد</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="analysis-count-badge">
                                {{ $patient->analyses_count }} فحص
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="d-flex gap-2 justify-content-center">
                                <a href="{{ route('dashboard.patients.show', $patient) }}" class="btn-action bg-primary bg-opacity-10 text-primary" title="عرض الملف">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <a href="{{ route('dashboard.patients.edit', $patient) }}" class="btn-action bg-secondary bg-opacity-10 text-secondary" title="تعديل">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <form action="{{ route('dashboard.patients.destroy', $patient) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا المريض؟ لا يمكن التراجع عن هذا الإجراء.')">
                                    @csrf 
                                    @method('DELETE')
                                    <button class="btn-action bg-danger bg-opacity-10 text-danger border-0" title="حذف">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <div class="py-4">
                                <i class="fa-solid fa-user-slash fs-1 text-muted mb-3 d-block"></i>
                                <h5 class="text-muted">لا توجد سجلات مرضى مطابقة للبحث</h5>
                                <p class="small text-muted">حاول تغيير كلمات البحث أو الفلترة</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="d-flex justify-content-center mt-5">
    {{ $patients->links() }}
</div>

@endsection