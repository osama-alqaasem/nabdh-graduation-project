@extends('layouts.app')

@section('title', 'تقارير التحليل الذكي | نبض AI')
@section('page_title', 'مركز التقارير الطبية')

@section('content')
@php
    $labelOptions = [
        'طبيعي' => 'طبيعي',
        'اضطراب بنبض القلب' => 'اضطراب بنبض القلب',
        'مصاب بجلطة' => 'مصاب بجلطة',
        'مصاب بجلطة سابقة' => 'مصاب بجلطة سابقة',
    ];
@endphp

<style>
    /* تنسيقات حصرية لمركز التقارير */
    .reports-header {
        background: white;
        padding: 30px;
        border-radius: 30px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.03);
        margin-bottom: 30px;
    }

    .btn-pdf-export {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white !important;
        border: none;
        padding: 12px 25px;
        border-radius: 16px;
        font-weight: 700;
        box-shadow: 0 10px 20px rgba(239, 68, 68, 0.3);
        transition: 0.3s;
    }

    .btn-pdf-export:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 30px rgba(239, 68, 68, 0.4);
    }

    .filter-section {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(10px);
        border-radius: 25px;
        border: 1px solid rgba(255,255,255,0.4);
        padding: 25px;
    }

    .report-table-card {
        border-radius: 30px !important;
        border: none;
        box-shadow: 0 15px 50px rgba(0,0,0,0.05);
        overflow: hidden;
    }

    .table thead th {
        background-color: #f8fafc;
        padding: 20px;
        font-size: 0.85rem;
        color: #64748b;
        text-transform: uppercase;
        border: none;
    }

    .table tbody td {
        padding: 18px 20px;
        border-bottom: 1px solid #f1f5f9;
        font-weight: 500;
    }

    .status-pill {
        padding: 6px 14px;
        border-radius: 10px;
        font-weight: 700;
        font-size: 0.8rem;
    }

    .date-input {
        background-repeat: no-repeat;
        background-position: left 12px center;
        background-size: 20px;
    }

    .analysis-id {
        width: 40px;
        height: 40px;
        background: #f1f5f9;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        color: #475569;
    }
</style>

<div class="container-fluid">
    
    <div class="reports-header d-flex flex-wrap justify-content-between align-items-center gap-3 animate__animated animate__fadeIn">
        <div>
            <h2 class="fw-bold text-dark mb-1">مركز التقارير التحليلية</h2>
            <p class="text-muted mb-0"><i class="fa-solid fa-circle-info text-primary me-1"></i> تصفية واستخراج تقارير شاملة عن نتائج فحص القلب</p>
        </div>
        <a href="{{ route('dashboard.reports.pdf', request()->query()) }}" class="btn-pdf-export">
            <i class="fa-solid fa-file-pdf ms-2"></i> تصدير التقرير الحالي (PDF)
        </a>
    </div>

    <div class="card filter-section mb-4 shadow-sm">
        <div class="card-body p-2">
            <form class="row g-3" method="GET">
                <div class="col-md-3">
                    <label class="form-label small fw-bold"><i class="fa-solid fa-filter me-1 text-primary"></i> النتيجة التشخيصية</label>
                    <select name="label" class="form-select form-select-lg rounded-4 border-light shadow-none">
                        <option value="">جميع نتائج AI</option>
                        @foreach($labelOptions as $value => $text)
                            <option value="{{ $value }}" @selected($label === $value)>{{ $text }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label small fw-bold"><i class="fa-solid fa-hospital-user me-1 text-primary"></i> المريض</label>
                    <select name="patient_id" class="form-select form-select-lg rounded-4 border-light shadow-none">
                        <option value="">بحث عن مريض محدد...</option>
                        @foreach($patients as $patient)
                            <option value="{{ $patient->id }}" @selected($patientId == $patient->id)>{{ $patient->full_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label small fw-bold"><i class="fa-solid fa-calendar-alt me-1 text-primary"></i> من تاريخ</label>
                    <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-control form-control-lg rounded-4 border-light shadow-none date-input">
                </div>

                <div class="col-md-2">
                    <label class="form-label small fw-bold"><i class="fa-solid fa-calendar-alt me-1 text-primary"></i> إلى تاريخ</label>
                    <input type="date" name="date_to" value="{{ $dateTo }}" class="form-control form-control-lg rounded-4 border-light shadow-none date-input">
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-primary btn-lg w-100 rounded-4 fw-bold shadow-sm">
                        <i class="fa-solid fa-magnifying-glass me-2"></i> تطبيق
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card report-table-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">ID</th>
                            <th>المريض</th>
                            <th>المستخدم المسؤول</th>
                            <th>النتيجة النهائية</th>
                            <th>نسبة الدقة</th>
                            <th class="text-center">تاريخ الفحص</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($analyses as $analysis)
                            @php $resLabel = $analysis->result?->label ?? 'طبيعي'; @endphp
                            <tr class="hover-bg-light transition">
                                <td class="ps-4">
                                    <div class="analysis-id">{{ $analysis->id }}</div>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $analysis->patient?->full_name }}</div>
                                    <!-- <div class="extra-small text-muted">ID: #{{ $analysis->patient_id }}</div> -->
                                </td>
                                <td>
                                    <span class="small"><i class="fa-solid fa-user-doctor me-1 text-muted"></i> {{ $analysis->user?->name }}</span>
                                </td>
                                <td>
                                    @php
                                        $badgeClass = in_array($resLabel, ['مصاب بجلطة', 'مصاب بجلطة سابقة']) ? 'danger' : ($resLabel === 'اضطراب بنبض القلب' ? 'warning' : 'success');
                                    @endphp
                                    <span class="status-pill bg-{{ $badgeClass }} bg-opacity-10 text-{{ $badgeClass }}">
                                        {{ $resLabel }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="fw-bold text-primary">{{ $analysis->result?->probability ?? $analysis->result_score ?? '-' }}</span>
                                        <div class="progress flex-grow-1 d-none d-xl-flex" style="height: 4px; width: 60px;">
                                            <div class="progress-bar bg-primary" style="width: 85%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark rounded-pill border fw-normal px-3">
                                        {{ optional($analysis->created_at)->format('Y-m-d') }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="py-4">
                                        <img src="https://cdn-icons-png.flaticon.com/512/6598/6598519.png" style="width: 80px; opacity: 0.3;" class="mb-3">
                                        <h5 class="text-muted">لم نتمكن من العثور على تقارير مطابقة</h5>
                                        <p class="small text-muted">جرب تعديل خيارات التصفية أو التواريخ</p>
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
        {{ $analyses->links() }}
    </div>

</div>
@endsection