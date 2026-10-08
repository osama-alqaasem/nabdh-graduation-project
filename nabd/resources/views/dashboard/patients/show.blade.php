@extends('layouts.app')

@section('title', 'تفاصيل المريض | نبض AI')
@section('page_title', 'الملف الطبي للمريض')

@section('content')
<style>
    /* التنسيقات الخاصة بصفحة التفاصيل */
    .patient-profile-card {
        background: white;
        border-radius: 30px !important;
        border: none;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.05);
    }
    
    .avatar-ui-lg {
        width: 100px; height: 100px; border-radius: 30px;
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        color: white; display: flex; align-items: center; justify-content: center;
        font-size: 2.5rem; font-weight: bold; margin: 0 auto;
        box-shadow: 0 10px 20px rgba(79, 172, 254, 0.3);
    }

    .analysis-card {
        background: white;
        border-radius: 25px !important;
        border: 1px solid rgba(0,0,0,0.03) !important;
        transition: 0.3s;
    }

    .analysis-card:hover {
        transform: scale(1.01);
        border-color: #4facfe !important;
    }

    .ecg-preview-container {
        border-radius: 20px;
        overflow: hidden;
        border: 2px solid #f1f1f1;
        background: #fafafa;
        cursor: zoom-in;
    }

    .ecg-preview-container img {
        width: 100%; height: 220px; object-fit: contain; background: #000;
    }

    .status-badge-lg {
        padding: 10px 20px; border-radius: 15px; font-weight: 700;
    }

    .symptom-tag {
        background: #f8fafc; border: 1px solid #e2e8f0;
        padding: 5px 12px; border-radius: 10px; font-size: 0.85rem; color: #475569;
    }

    .info-label { color: #94a3b8; font-size: 0.85rem; margin-bottom: 2px; }
    .info-value { color: #1e293b; font-weight: 700; }

    /* تنسيق المؤشرات الحيوية الجديد */
    .section-title-medical {
        font-size: 0.95rem; font-weight: 800; color: #1e293b;
        border-right: 4px solid #4facfe; padding-right: 10px;
    }
    .info-item {
        display: flex; justify-content: space-between; align-items: center;
        padding: 8px 12px; background: #f8fafc; border-radius: 12px; margin-bottom: 5px;
    }
</style>

<div class="container-fluid">
    <div class="row g-4">
        
        <!-- بطاقة معلومات المريض -->
        <div class="col-lg-4">
            <div class="card patient-profile-card sticky-top" style="top: 20px;">
                <div class="card-body p-4 text-center">
                    <div class="avatar-ui-lg mb-4">
                        {{ mb_substr($patient->full_name, 0, 1) }}
                    </div>
                    
                    <h3 class="fw-bold mb-1">{{ $patient->full_name }}</h3>
                    <p class="text-muted mb-4 small"><i class="fa-solid fa-user-doctor me-1"></i> المسؤول: {{ $patient->user?->name }}</p>

                    <hr class="opacity-50">

                    <div class="row g-3 text-start mt-2">
                        <div class="col-6">
                            <div class="info-label">الجنس</div>
                            <div class="info-value"><i class="fa-solid fa-venus-mars me-2 text-primary"></i> {{ $patient->gender }}</div>
                        </div>
                        <div class="col-6">
                            <div class="info-label">العمر</div>
                            <div class="info-value"><i class="fa-solid fa-calendar-check me-2 text-primary"></i> {{ $patient->age }} سنة</div>
                        </div>
                        <div class="col-12 mt-3">
                            <div class="p-3 rounded-4 bg-light text-center">
                                <div class="info-label">إجمالي التحاليل المجرية</div>
                                <div class="fs-4 fw-bold text-primary">{{ $patient->analyses->count() }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('dashboard.patients.index') }}" class="btn btn-outline-secondary w-100 rounded-pill">
                            <i class="fa-solid fa-arrow-right me-2"></i> العودة للقائمة
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- سجل التحاليل -->
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold mb-0">سجل التحاليل الذكية</h4>
                <span class="badge bg-white text-dark border rounded-pill px-3 shadow-sm">{{ $patient->analyses->count() }} فحص</span>
            </div>

            @forelse($patient->analyses->sortByDesc('created_at') as $analysis)
                <div class="card analysis-card mb-4">
                    <div class="card-body p-4">
                        
                        <div class="d-flex justify-content-between align-items-start mb-4">
                            <div>
                                <span class="text-primary fw-bold small">تحليل رقم #{{ $analysis->id }}</span>
                                <div class="text-muted small"><i class="fa-solid fa-clock me-1"></i> {{ optional($analysis->created_at)->format('Y-m-d | h:i A') }}</div>
                            </div>
                            
                            @php
                                // جلب النتيجة والدقة بشكل ديناميكي
                                $label = $analysis->result?->label ?? $analysis->result_text ?? 'غير محدد';
                                $probability = $analysis->result?->probability ?? $analysis->result_score ?? 0;
                                
                                $bgClass = match(trim($label)) {
                                    'مصاب بجلطة', 'مصاب بجلطة سابقة', 'Heart Attack' => 'danger',
                                    'اضطراب بنبض القلب', 'Arrhythmia' => 'warning',
                                    default => 'success'
                                };
                                
                                $icon = match($bgClass) {
                                    'danger' => 'fa-triangle-exclamation',
                                    'warning' => 'fa-wave-square',
                                    default => 'fa-circle-check'
                                };
                            @endphp
                            
                            <div class="status-badge-lg bg-{{ $bgClass }} bg-opacity-10 text-{{ $bgClass }}">
                                <i class="fa-solid {{ $icon }} me-2"></i> {{ $label }}
                            </div>
                        </div>

                        <div class="row g-4">
                            <div class="col-md-6">
                                <!-- مؤشرات الدقة -->
                                <div class="mb-4">
                                    <h6 class="fw-bold mb-3"><i class="fa-solid fa-clipboard-list me-2"></i> مؤشرات التشخيص:</h6>
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <div class="small text-muted">دقة التشخيص:</div>
                                        <div class="fw-bold text-dark">{{ number_format($probability, 1) }}%</div>
                                    </div>
                                    <div class="progress" style="height: 6px; width: 150px;">
                                        <div class="progress-bar bg-{{ $bgClass }}" style="width: {{ $probability }}%"></div>
                                    </div>
                                </div>

                                <!-- الأعراض -->
                                <div class="mb-4">
                                    <h6 class="fw-bold mb-3"><i class="fa-solid fa-mask-face me-2"></i> الأعراض المسجلة:</h6>
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
                                            <span class="symptom-tag">
                                                <i class="fa-solid {{ $symptomLabels[$symptom->name][1] ?? 'fa-check' }} me-1 text-primary"></i>
                                                {{ $symptomLabels[$symptom->name][0] ?? $symptom->name }}
                                            </span>
                                        @empty
                                            <span class="text-muted small italic">لا توجد أعراض مسجلة</span>
                                        @endforelse
                                    </div>
                                </div>

                                <!-- المؤشرات الحيوية (القسم المطلوب) -->
                                <h6 class="section-title-medical mb-3">المؤشرات الحيوية</h6>
                                <div class="vstack gap-1">
                                    <div class="info-item">
                                        <span class="info-label mb-0">Troponin</span>
                                        <span class="info-value text-danger">{{ $analysis->troponin ?? '-' }} <small class="fw-normal text-muted">ng/mL</small></span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label mb-0">CK-MB</span>
                                        <span class="info-value text-info">{{ $analysis->ckmb ?? '-' }} <small class="fw-normal text-muted">ng/mL</small></span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <h6 class="fw-bold mb-3"><i class="fa-solid fa-heart-pulse me-2"></i> تخطيط القلب (ECG):</h6>
                                <div class="ecg-preview-container shadow-sm">
                                    @if($analysis->ecgImage && $analysis->ecgImage->path)
                                        <img src="{{ asset('storage/' . $analysis->ecgImage->path) }}" alt="ECG Scan">
                                        <div class="p-2 text-center bg-white border-top">
                                            <span class="extra-small text-muted">اضغط لتكبير الصورة <i class="fa-solid fa-magnifying-glass-plus ms-1"></i></span>
                                        </div>
                                    @else
                                        <div class="d-flex flex-column align-items-center justify-content-center h-100 bg-light text-muted p-5" style="min-height: 220px;">
                                            <i class="fa-solid fa-image-slash fs-2 mb-2"></i>
                                            <span class="small">لا توجد صورة متوفرة</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            @empty
                <div class="card patient-profile-card text-center py-5">
                    <div class="card-body">
                        <img src="https://cdn-icons-png.flaticon.com/512/6598/6598519.png" style="width: 80px;" class="mb-3 opacity-25">
                        <h5 class="text-muted">السجل الطبي فارغ حالياً</h5>
                        <p class="small text-muted">لم يتم إجراء أي تحاليل لهذا المريض بعد.</p>
                    </div>
                </div>
            @endforelse
        </div>

    </div>
</div>
@endsection