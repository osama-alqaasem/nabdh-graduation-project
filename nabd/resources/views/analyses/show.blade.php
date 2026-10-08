@extends('layouts.app')

@section('title', 'تقرير التحليل الذكي | نبض AI')
@section('page_title', 'تفاصيل التقرير الطبي')

@section('content')
<style>
    .detail-card {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(15px);
        border-radius: 30px !important;
        border: 1px solid rgba(255, 255, 255, 0.4) !important;
        box-shadow: 0 15px 35px rgba(0,0,0,0.05) !important;
    }

    .info-item {
        padding: 12px 15px;
        background: #f8fafc;
        border-radius: 15px;
        margin-bottom: 10px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .info-label {
        color: #64748b;
        font-weight: 700;
        font-size: 0.85rem;
    }

    .info-value {
        color: #1e293b;
        font-weight: 800;
    }

    .ecg-container {
        background: #000;
        border-radius: 20px;
        overflow: hidden;
        position: relative;
        border: 4px solid #f1f5f9;
    }

    .ecg-image {
        width: 100%;
        max-height: 400px;
        object-fit: contain;
        transition: 0.3s;
    }

    .ecg-container:hover .ecg-image {
        transform: scale(1.02);
    }

    .ai-score-badge {
        background: #fff;
        border: 1px solid #e2e8f0;
        padding: 10px 15px;
        border-radius: 12px;
        flex: 1;
        min-width: 120px;
        text-align: center;
    }

    .section-title-medical {
        font-size: 1.1rem;
        font-weight: 800;
        color: #334155;
        border-right: 4px solid #6366f1;
        padding-right: 12px;
        margin-bottom: 20px;
    }

    .data-list-item {
        border-bottom: 1px dashed #e2e8f0;
        padding: 8px 0;
    }

    .data-list-item:last-child {
        border-bottom: none;
    }
</style>

<div class="container pb-5 animate__animated animate__fadeIn">
    <div class="row g-4">
        <!-- عمود البيانات الأساسية -->
        <div class="col-lg-4">
            <div class="card detail-card h-100">
                <div class="card-body p-4">
                    <h5 class="section-title-medical mb-4">بيانات التحليل الأساسية</h5>
                    
                    <div class="vstack gap-1">
                        <div class="info-item">
                            <span class="info-label">المريض</span>
                            <span class="info-value text-primary">{{ $analysis->patient?->full_name }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">المسؤول عن المريض</span>
                            <span class="info-value">{{ $analysis->user?->name }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">العمر وقت الفحص</span>
                            <span class="info-value">{{ $analysis->age }} عام</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">تاريخ التحليل</span>
                            <span class="info-value">{{ $analysis->created_at?->format('Y-m-d') }}</span>
                        </div>
                    </div>

                    <h5 class="section-title-medical mt-5 mb-4">المؤشرات الحيوية</h5>
                    <div class="vstack gap-1">
                        <div class="info-item">
                            <span class="info-label">Troponin</span>
                            <span class="info-value text-danger">{{ $analysis->troponin ?? '-' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">CK-MB</span>
                            <span class="info-value text-info">{{ $analysis->ckmb ?? '-' }}</span>
                        </div>
                    </div>

                    <div class="mt-5 p-4 rounded-4 bg-primary bg-opacity-10 border border-primary border-opacity-20 text-center">
                        <div class="small fw-bold text-primary mb-2">التشخيص النهائي</div>
                        @php
                            $label = $analysis->result?->label ?? $analysis->result_text ?? 'طبيعي';
                            $badge = match($label) {
                                'مصاب بجلطة', 'مصاب بجلطة سابقة' => 'danger',
                                'اضطراب بنبض القلب' => 'warning',
                                default => 'success'
                            };
                        @endphp
                        <h4 class="fw-extrabold text-{{ $badge }} mb-0">{{ $label }}</h4>
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('dashboard.analyses.edit', $analysis) }}" class="btn btn-dark w-100 rounded-pill mb-2">
                            <i class="fa-solid fa-pen-to-square ms-1"></i> تعديل التقرير
                        </a>
                        <a href="{{ route('dashboard.analyses.index') }}" class="btn btn-outline-secondary w-100 rounded-pill">
                            <i class="fa-solid fa-arrow-right ms-1"></i> العودة للسجل
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- عمود التخطيط والنتائج التفصيلية -->
        <div class="col-lg-8">
            <div class="card detail-card h-100">
                <div class="card-body p-4 p-lg-5">
                    
                    <h5 class="section-title-medical mb-4">تخطيط القلب الكهربائي (ECG)</h5>
                    <div class="ecg-container mb-5 shadow-sm">
                        @if($analysis->ecgImage)
                            <img src="{{ $analysis->ecgImage->url }}" class="ecg-image" alt="ECG">
                        @elseif($analysis->ecg_image_path)
                            <img src="{{ asset('storage/' . $analysis->ecg_image_path) }}" class="ecg-image" alt="ECG">
                        @else
                            <div class="text-center py-5 text-white bg-dark bg-opacity-50">
                                <i class="fa-solid fa-heart-pulse fs-1 mb-2"></i>
                                <p>لم يتم إرفاق صورة تخطيط القلب لهذا التحليل</p>
                            </div>
                        @endif
                    </div>

                    <div class="row g-4">
                        <div class="col-md-6">
                            <h6 class="fw-extrabold text-dark mb-3"><i class="fa-solid fa-notes-medical text-primary me-2"></i> الأعراض المرصودة</h6>
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
                            <div class="p-3 rounded-4 border bg-light bg-opacity-50 h-100">
                                @forelse($analysis->symptoms as $symptom)
                                    <div class="d-flex justify-content-between data-list-item">
                                        <span class="fw-bold small text-secondary">
                                            <i class="fa-solid {{ $symptomLabels[$symptom->name][1] ?? 'fa-check' }} text-primary me-1"></i>
                                            {{ $symptomLabels[$symptom->name][0] ?? $symptom->name }}
                                        </span>
                                        <span class="badge bg-white text-dark border shadow-sm rounded-pill px-3">{{ (int) $symptom->pivot->value }}</span>
                                    </div>
                                @empty
                                    <div class="text-muted small text-center py-3">لا توجد أعراض مسجلة.</div>
                                @endforelse
                            </div>
                        </div>

                      

                    @if($analysis->result)
                        <div class="mt-5 p-4 rounded-4" style="background: linear-gradient(135deg, #f8fafc 0%, #eff6ff 100%); border: 1px solid #e2e8f0;">
                            <div class="fw-extrabold mb-4 text-dark"><i class="fa-solid fa-microchip text-primary me-2"></i> تحليل الطبقات الذكي (Multi-modal AI)</div>
                            <div class="d-flex flex-wrap gap-3">
                                <div class="ai-score-badge">
                                    <div class="small text-muted mb-1">الاحتمالية</div>
                                    <div class="fw-extrabold text-primary">{{ $analysis->result->probability ?? '-' }}%</div>
                                </div>
                                <div class="ai-score-badge">
                                    <div class="small text-muted mb-1">تحليل الصورة</div>
                                    <div class="fw-extrabold">{{ $analysis->result->image_score ?? '-' }}</div>
                                </div>
                                <div class="ai-score-badge">
                                    <div class="small text-muted mb-1">البيانات الرقمية</div>
                                    <div class="fw-extrabold">{{ $analysis->result->tabular_score ?? '-' }}</div>
                                </div>
                                <div class="ai-score-badge">
                                    <div class="small text-muted mb-1">وزن الأعراض</div>
                                    <div class="fw-extrabold">{{ $analysis->result->symptoms_score ?? '-' }}</div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($analysis->result_text)
                        
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection