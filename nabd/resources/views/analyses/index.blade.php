@extends('layouts.app')

@section('title', 'التحاليل الطبية | نبض AI')
@section('page_title', 'سجل التحاليل الذكية')

@section('content')
<style>
    /* تنسيقات حصرية لصفحة التحاليل */
    .analysis-filter-card {
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(15px);
        border-radius: 25px !important;
        border: 1px solid rgba(255, 255, 255, 0.4);
        box-shadow: 0 10px 30px rgba(0,0,0,0.02);
    }

    .analysis-table-card {
        border-radius: 35px !important;
        border: none;
        box-shadow: 0 20px 60px rgba(0,0,0,0.05);
        overflow: hidden;
        background: rgba(255, 255, 255, 0.95);
    }

    .table thead th {
        background-color: #f8fafc;
        color: #64748b;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.8rem;
        padding: 20px;
        border: none;
        letter-spacing: 0.5px;
    }

    .table tbody td {
        padding: 20px;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        color: #1e293b;
        font-weight: 500;
    }

    .status-badge-premium {
        padding: 8px 16px;
        border-radius: 12px;
        font-weight: 700;
        font-size: 0.75rem;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .probability-text {
        font-family: 'Poppins', sans-serif;
        font-weight: 700;
        color: #6366f1;
    }

    .btn-action-custom {
        width: 35px;
        height: 35px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: none;
    }

    .btn-action-custom:hover {
        transform: translateY(-3px) scale(1.1);
    }

    .patient-info-box {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .patient-avatar-sm {
        width: 38px;
        height: 38px;
        background: #f1f5f9;
        color: #6366f1;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 0.9rem;
    }

    .form-control-modern, .form-select-modern {
        border-radius: 15px !important;
        border: 1.5px solid #e2e8f0 !important;
        padding-right: 45px !important;
        background-color: #f8fafc !important;
        transition: 0.3s;
    }

    .form-control-modern:focus {
        border-color: #6366f1 !important;
        background-color: #fff !important;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1) !important;
    }

    .search-icon-inside {
        position: absolute;
        right: 18px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        z-index: 5;
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-5 gap-3 flex-wrap animate__animated animate__fadeIn">
    <div>
        <h2 class="fw-extrabold text-dark mb-1">سجل التحاليل 🧪</h2>
        <p class="text-muted mb-0 small">مراقبة وتدقيق نتائج تشخيص القلب المدعومة بالذكاء الاصطناعي</p>
    </div>
    <a href="{{ route('dashboard.analyses.create') }}" class="btn btn-primary rounded-pill px-4 shadow-lg fw-bold py-2">
        <i class="fa-solid fa-plus-circle ms-2"></i> إضافة تحليل جديد
    </a>
</div>

<div class="card analysis-filter-card mb-4 animate__animated animate__fadeIn">
    <div class="card-body p-4">
        <form class="row g-3" method="GET">
            <div class="col-md-4 position-relative">
                <i class="fa-solid fa-magnifying-glass search-icon-inside"></i>
                <input type="search" name="q" value="{{ $q }}" class="form-control form-control-lg form-control-modern border-0" placeholder="بحث باسم المريض...">
            </div>
            <div class="col-md-3">
                <select class="form-select form-select-lg form-select-modern border-0" name="user_id">
                    <option value="">جميع الأطباء/المسؤولين</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" @selected($userId == $user->id)>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select form-select-lg form-select-modern border-0" name="patient_id">
                    <option value="">جميع المرضى</option>
                    @foreach($patients as $patient)
                        <option value="{{ $patient->id }}" @selected($patientId == $patient->id)>{{ $patient->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-dark btn-lg w-100 rounded-4 fw-bold shadow-sm">تصفية النتائج</button>
            </div>
        </form>
    </div>
</div>

<div class="card analysis-table-card animate__animated animate__fadeInUp">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">كود التحليل</th>
                        <th>المريض</th>
                        <th>المستخدم المسؤول</th>
                        <th class="text-center">نتيجة AI</th>
                        <th class="text-center">الاحتمالية</th>
                        <th class="text-center">التاريخ</th>
                        <th class="text-center rounded-end-4 pe-4">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($analyses as $analysis)
                    <tr>
                        <td class="ps-4">
                            <span class="badge bg-light text-secondary rounded-pill border px-3">#{{ $analysis->id }}</span>
                        </td>
                        <td>
                            <div class="patient-info-box">
                                <div class="patient-avatar-sm">
                                    {{ mb_substr($analysis->patient?->full_name, 0, 1) }}
                                </div>
                                <div>
                                    <div class="fw-bold">{{ $analysis->patient?->full_name }}</div>
                                    <!-- <small class="text-muted" style="font-size: 0.7rem;">ID: P-{{ $analysis->patient_id }}</small> -->
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="small fw-semibold text-muted">
                                <i class="fa-solid fa-user-doctor me-1"></i> {{ $analysis->user?->name }}
                            </div>
                        </td>
                        <td class="text-center">
                            @php
                                $label = $analysis->result?->label ?? $analysis->result_text ?? 'غير محدد';
                                $color = match($label) {
                                    'مصاب بجلطة', 'مصاب بجلطة سابقة' => 'danger',
                                    'اضطراب بنبض القلب' => 'warning',
                                    'طبيعي' => 'success',
                                    default => 'secondary'
                                };
                            @endphp
                            <span class="status-badge-premium bg-{{ $color }} bg-opacity-10 text-{{ $color }}">
                                <i class="fa-solid fa-circle shadow-sm"></i> {{ $label }}
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="probability-text">
                                {{ $analysis->result?->probability ?? $analysis->result_score ?? '-' }}%
                            </div>
                        </td>
                        <td class="text-center">
                            <div class="small text-muted fw-bold">
                                {{ optional($analysis->created_at)->format('Y-m-d') }}
                            </div>
                        </td>
                        <td class="pe-4">
                            <div class="d-flex gap-2 justify-content-center">
                                <a href="{{ route('dashboard.analyses.show', $analysis) }}" class="btn-action-custom bg-primary bg-opacity-10 text-primary" title="عرض التقرير">
                                    <i class="fa-solid fa-file-medical"></i>
                                </a>
                                <a href="{{ route('dashboard.analyses.edit', $analysis) }}" class="btn-action-custom bg-secondary bg-opacity-10 text-secondary" title="تعديل">
                                    <i class="fa-solid fa-pen-nib"></i>
                                </a>
                                <form action="{{ route('dashboard.analyses.destroy', $analysis) }}" method="POST" class="d-inline" onsubmit="return confirm('حذف هذا التحليل نهائياً؟')">
                                    @csrf 
                                    @method('DELETE')
                                    <button class="btn-action-custom bg-danger bg-opacity-10 text-danger" title="حذف">
                                        <i class="fa-solid fa-trash-alt"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="py-4">
                                <i class="fa-solid fa-microscope fs-1 text-muted opacity-20 d-block mb-3"></i>
                                <h6 class="text-muted fw-bold">لا يوجد تحاليل مطابقة لمعايير البحث</h6>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-5 d-flex justify-content-center">
    {{ $analyses->links() }}
</div>
@endsection