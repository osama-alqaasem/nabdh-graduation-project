@extends('layouts.app')

@section('title', 'ملف المريض | نبض AI')
@section('page_title', 'التفاصيل الطبية للمريض')

@section('content')
<style>
    /* تحسينات إضافية خاصة بصفحة التفاصيل */
    .patient-header-card {
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(240, 245, 255, 0.9) 100%);
        border-radius: 30px !important;
        border: 1px solid rgba(255, 255, 255, 0.4) !important;
    }

    .info-item {
        background: rgba(255, 255, 255, 0.5);
        padding: 12px 20px;
        border-radius: 18px;
        border: 1px solid rgba(99, 102, 241, 0.1);
        display: flex;
        align-items: center;
        gap: 12px;
        transition: 0.3s;
    }

    .info-item i {
        color: #4f46e5;
        font-size: 1.2rem;
    }

    .analysis-item-card {
        border-radius: 25px !important;
        border: 1px solid rgba(0, 0, 0, 0.05) !important;
        transition: all 0.3s ease;
    }

    .analysis-item-card:hover {
        transform: scale(1.01);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08) !important;
    }

    .ecg-frame {
        border-radius: 20px;
        overflow: hidden;
        border: 2px solid #eef2ff;
        background: #f8fafc;
        box-shadow: inset 0 0 15px rgba(0,0,0,0.05);
    }

    .ecg-frame img {
        max-height: 250px;
        width: 100%;
        object-fit: contain;
        transition: 0.5s;
    }

    .ecg-frame:hover img {
        transform: scale(1.05);
    }

    .symptom-tag {
        background: #fff;
        border: 1px solid #e2e8f0;
        padding: 6px 15px;
        border-radius: 12px;
        font-size: 0.85rem;
        font-weight: 600;
        color: #475569;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .probability-badge {
        font-family: 'monospace';
        font-size: 1.1rem;
        color: #1e293b;
        font-weight: bold;
    }
</style>

<div class="row g-4 mb-5">
    <div class="col-lg-4">
        <div class="card patient-header-card shadow-sm h-100">
            <div class="card-body p-4 text-center">
                <div class="avatar-circle mx-auto mb-4 shadow" style="width:110px; height:110px; font-size:2.5rem; background: var(--primary-gradient); color: white;">
                    {{ mb_substr($patient->full_name, 0, 1) }}
                </div>

                <h3 class="fw-bold text-dark mb-1">{{ $patient->full_name }}</h3>
                <p class="text-muted mb-4 small"><i class="fa-solid fa-id-card me-1"></i> معرف المريض: #PAT-{{ $patient->id }}</p>

                <div class="vstack gap-3 text-start">
                    <div class="info-item">
                        <i class="fa-solid fa-venus-mars"></i>
                        <div>
                            <small class="text-muted d-block">الجنس</small>
                            <span class="fw-bold">{{ $patient->gender == 'male' ? 'ذكر' : 'أنثى' }}</span>
                        </div>
                    </div>

                    <div class="info-item">
                        <i class="fa-solid fa-calendar-check"></i>
                        <div>
                            <small class="text-muted d-block">العمر</small>
                            <span class="fw-bold">{{ $patient->age }} سنة</span>
                        </div>
                    </div>

                    <div class="info-item">
                        <i class="fa-solid fa-user-doctor"></i>
                        <div>
                            <small class="text-muted d-block">المسؤول عن الحالة</small>
                            <span class="fw-bold">{{ $patient->user?->name ?? 'غير محدد' }}</span>
                        </div>
                    </div>

                    <div class="info-item bg-primary bg-opacity-10">
                        <i class="fa-solid fa-file-waveform text-primary"></i>
                        <div>
                            <small class="text-muted d-block">إجمالي التحاليل</small>
                            <span class="fw-bold text-primary">{{ $patient->analyses->count() }} فحص طبي</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold mb-0 text-dark">سجل الفحوصات والتحاليل</h4>
            <a href="{{ route('dashboard.patients.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                <i class="fa-solid fa-arrow-right me-1"></i> العودة للقائمة
            </a>
        </div>

        @forelse($patient->analyses as $analysis)
            <div class="card glass-card analysis-item-card mb-4 shadow-sm">
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-md-6 border-end border-light">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <span class="badge bg-light text-dark border mb-2">رقم التحليل: #{{ $analysis->id }}</span>
                                    <div class="text-muted small"><i class="fa-solid fa-calendar-day me-1"></i> {{ optional($analysis->created_at)->translatedFormat('d M Y | h:i A') }}</div>
                                </div>
                                
                                @php($label = $analysis->result?->label ?? $analysis->result_text ?? 'طبيعي')
                                @php($statusData = match($label) {
                                    'مصاب بجلطة', 'مصاب بجلطة سابقة' => ['danger', 'fa-triangle-exclamation'],
                                    'اضطراب بنبض القلب' => ['warning', 'fa-wave-square'],
                                    default => ['success', 'fa-circle-check']
                                })

                                <span class="badge rounded-pill px-3 py-2 bg-{{ $statusData[0] }} bg-opacity-10 text-{{ $statusData[0] }} border border-{{ $statusData[0] }} border-opacity-25">
                                    <i class="fa-solid {{ $statusData[1] }} me-1"></i> {{ $label }}
                                </span>
                            </div>

                            <div class="row g-3 mb-4 mt-2">
                                <div class="col-6">
                                    <small class="text-muted d-block">نسبة اليقين (AI)</small>
                                    <span class="probability-badge">{{ $analysis->result?->probability ?? $analysis->result_score ?? '98.5' }}%</span>
                                </div>
                            </div>

                            <div class="mt-4">
                                <h6 class="fw-bold mb-3 small text-uppercase" style="letter-spacing: 1px;">الأعراض المرافقة</h6>
                                @php
                                    $symptomLabels = [
                                        'chest_pain' => ['ألم صدر', 'fa-hand-dots'],
                                        'shortness' => ['ضيق تنفس', 'fa-wind'],
                                        'shortness_of_breath' => ['ضيق تنفس', 'fa-wind'],
                                        'nausea' => ['غثيان', 'fa-face-frown-slight'],
                                        'dizziness' => ['دوخة', 'fa-spinner'],
                                        'sweating' => ['تعرق', 'fa-droplet'],
                                        'fatigue' => ['إرهاق', 'fa-bed'],
                                        'palpitations' => ['خفقان القلب', 'fa-heart-pulse'],
                                    ];
                                @endphp
                                <div class="d-flex flex-wrap gap-2">
                                    @forelse($analysis->symptoms as $symptom)
                                        <div class="symptom-tag">
                                            <i class="fa-solid {{ $symptomLabels[$symptom->name][1] ?? 'fa-check' }} text-primary small"></i>
                                            {{ $symptomLabels[$symptom->name][0] ?? $symptom->name }}
                                        </div>
                                    @empty
                                        <div class="text-muted small italic p-2 bg-light rounded-3 w-100 text-center">لا توجد أعراض مسجلة</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <h6 class="fw-bold mb-3 small text-uppercase" style="letter-spacing: 1px;">صورة تخطيط القلب (ECG)</h6>
                            <div class="ecg-frame p-2">
                                @if($analysis->ecg_image_path)
                                    <img src="{{ asset('storage/' . $analysis->ecg_image_path) }}" alt="ECG Scan Result">
                                @else
                                    <div class="d-flex flex-column align-items-center justify-content-center py-5 text-muted">
                                        <i class="fa-solid fa-image-slash fs-2 mb-2"></i>
                                        <span class="small">لا توجد صورة متوفرة</span>
                                    </div>
                                @endif
                            </div>
                            <div class="mt-2 text-center">
                                @if($analysis->ecg_image_path)
                                    <a href="{{ asset('storage/' . $analysis->ecg_image_path) }}" target="_blank" class="btn btn-sm btn-link text-decoration-none">
                                        <i class="fa-solid fa-magnifying-glass-plus me-1"></i> عرض بحجم كامل
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="card glass-card border-0 p-5 text-center shadow-sm">
                <i class="fa-solid fa-folder-open fs-1 text-muted opacity-25 mb-3"></i>
                <h5 class="text-muted">لا يوجد سجل تحاليل لهذا المريض حتى الآن</h5>
                <p class="small text-muted mb-0">يمكنك إضافة تحليل جديد من خلال قسم إدارة التحاليل</p>
            </div>
        @endforelse
    </div>
</div>
@endsection