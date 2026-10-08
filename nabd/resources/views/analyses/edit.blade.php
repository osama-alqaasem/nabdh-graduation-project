@extends('layouts.app')

@section('title', 'تعديل تحليل | نبض AI')
@section('page_title', 'تحديث بيانات التحليل الذكي')

@section('content')
@php 
    $selectedSymptoms = $analysis->symptoms->pluck('id')->all(); 
    $selectedTests = $analysis->tests->keyBy('id'); 
@endphp

<style>
    /* التنسيقات الحصرية لنموذج التحاليل */
    .analysis-edit-card {
        max-width: 1150px;
        margin: 0 auto;
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(15px);
        border-radius: 35px !important;
        border: 1px solid rgba(255, 255, 255, 0.4) !important;
        box-shadow: 0 25px 50px rgba(0, 0, 0, 0.05) !important;
    }

    .section-divider {
        position: relative;
        padding-right: 20px;
        margin-bottom: 25px;
        color: #6366f1;
        font-weight: 800;
        font-size: 1.1rem;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .section-divider::before {
        content: '';
        position: absolute;
        right: 0;
        height: 100%;
        width: 6px;
        background: linear-gradient(to bottom, #6366f1, #a855f7);
        border-radius: 10px;
    }

    .form-label {
        font-weight: 700;
        color: #334155;
        margin-bottom: 8px;
        font-size: 0.85rem;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .form-control-modern, .form-select-modern {
        border-radius: 16px !important;
        border: 1.5px solid #e2e8f0 !important;
        padding: 12px 18px !important;
        background-color: #f8fafc !important;
        transition: 0.3s ease-in-out;
    }

    .form-control-modern:focus, .form-select-modern:focus {
        border-color: #6366f1 !important;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1) !important;
        background-color: #fff !important;
    }

    .symptom-check-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        padding: 12px 15px;
        border-radius: 18px;
        transition: 0.3s;
        cursor: pointer;
        height: 100%;
    }

    .symptom-check-card:hover {
        border-color: #6366f1;
        background: rgba(99, 102, 241, 0.02);
    }

    .upload-preview-box {
        border: 2px dashed #cbd5e1;
        border-radius: 25px;
        padding: 20px;
        background: #f8fafc;
        text-align: center;
    }

    .btn-update-analysis {
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        border: none;
        padding: 15px 50px;
        border-radius: 20px;
        font-weight: 700;
        color: white;
        box-shadow: 0 10px 25px rgba(99, 102, 241, 0.3);
        transition: 0.3s;
    }

    .btn-update-analysis:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 35px rgba(99, 102, 241, 0.4);
    }
</style>

<div class="container pb-5 animate__animated animate__fadeIn">
    <div class="card analysis-edit-card shadow-sm">
        <div class="card-body p-4 p-lg-5">
            
            <form method="POST" action="{{ route('dashboard.analyses.update', $analysis) }}" enctype="multipart/form-data">
                @csrf 
                @method('PUT')

                <div class="section-divider">
                    <i class="fa-solid fa-hospital-user"></i> معلومات المريض والمسؤول
                </div>
                
                <div class="row g-4 mb-5">
                    <div class="col-md-4">
                        <label class="form-label"><i class="fa-solid fa-user-doctor text-primary"></i> الطبيب / المستخدم المسؤول</label>
                        <select name="user_id" class="form-select form-select-lg form-select-modern" required>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" @selected(old('user_id', $analysis->user_id) == $user->id)>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label"><i class="fa-solid fa-user-tag text-primary"></i> اسم المريض</label>
                        <select name="patient_id" class="form-select form-select-lg form-select-modern" required>
                            @foreach($patients as $patient)
                                <option value="{{ $patient->id }}" @selected(old('patient_id', $analysis->patient_id) == $patient->id)>{{ $patient->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label"><i class="fa-solid fa-calendar-check text-primary"></i> العمر وقت التحليل</label>
                        <input type="number" name="age" value="{{ old('age', $analysis->age) }}" class="form-control form-control-lg form-control-modern" required>
                    </div>
                </div>

                <div class="section-divider">
                    <i class="fa-solid fa-droplet"></i> المؤشرات المخبرية (Biomarkers)
                </div>
                <div class="row g-4 mb-5">
                    <div class="col-md-6">
                        <label class="form-label"><i class="fa-solid fa-vial text-danger"></i> مستوى التروپونين (Troponin)</label>
                        <input type="number" step="0.0001" name="troponin" value="{{ old('troponin', $analysis->troponin) }}" class="form-control form-control-lg form-control-modern" placeholder="0.0000">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"><i class="fa-solid fa-microscope text-info"></i> مستوى CK-MB</label>
                        <input type="number" step="0.0001" name="ckmb" value="{{ old('ckmb', $analysis->ckmb) }}" class="form-control form-control-lg form-control-modern" placeholder="0.0000">
                    </div>
                </div>

                <div class="section-divider">
                    <i class="fa-solid fa-brain"></i> مخرجات نظام نـبـض AI
                </div>
                <div class="p-4 rounded-4 bg-light bg-opacity-50 border mb-5">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label"><i class="fa-solid fa-comment-medical text-primary"></i> التشخيص النهائي (نص)</label>
                            <input type="text" name="result_text" value="{{ old('result_text', $analysis->result_text) }}" class="form-control form-control-lg form-control-modern" placeholder="مثال: طبيعي أو اشتباه جلطة">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label"><i class="fa-solid fa-percent text-primary"></i> النتيجة الإجمالية</label>
                            <input type="number" step="0.01" name="result_score" value="{{ old('result_score', $analysis->result_score) }}" class="form-control form-control-lg form-control-modern">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label"><i class="fa-solid fa-chart-line text-primary"></i> الاحتمالية (Prob.)</label>
                            <input type="number" step="0.01" name="probability" value="{{ old('probability', $analysis->result?->probability) }}" class="form-control form-control-lg form-control-modern">
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label small text-muted">Image Score (ECG Analysis)</label>
                            <input type="number" step="0.01" name="image_score" value="{{ old('image_score', $analysis->result?->image_score) }}" class="form-control form-control-modern">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted">Tabular Score (Bio-data)</label>
                            <input type="number" step="0.01" name="tabular_score" value="{{ old('tabular_score', $analysis->result?->tabular_score) }}" class="form-control form-control-modern">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted">Symptoms Weight Score</label>
                            <input type="number" step="0.01" name="symptoms_score" value="{{ old('symptoms_score', $analysis->result?->symptoms_score) }}" class="form-control form-control-modern">
                        </div>
                    </div>
                </div>

                <div class="row g-4 mb-5">
                    <div class="col-lg-6">
                        <div class="section-divider"><i class="fa-solid fa-stethoscope"></i> الأعراض السريرية</div>
                        <div class="p-3 rounded-4 bg-white border h-100 overflow-auto" style="max-height: 280px;">
                            <div class="row g-2">
                                @foreach($symptoms as $symptom)
                                    <div class="col-md-6">
                                        <div class="symptom-check-card">
                                            <div class="form-check mb-0">
                                                <input class="form-check-input" type="checkbox" name="symptoms[]" value="{{ $symptom->id }}" id="symp{{ $symptom->id }}" @checked(in_array($symptom->id, $selectedSymptoms))>
                                                <label class="form-check-label small fw-bold" for="symp{{ $symptom->id }}">{{ $symptom->name }}</label>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="section-divider"><i class="fa-solid fa-file-waveform"></i> فحص ECG</div>
                        <div class="upload-preview-box h-100 d-flex flex-column justify-content-center">
                            <div class="mb-3">
                                <label class="form-label justify-content-center"><i class="fa-solid fa-cloud-arrow-up"></i> تحديث صورة التخطيط</label>
                                <input type="file" name="ecg_image" class="form-control form-control-modern" accept="image/*">
                            </div>
                            @if($analysis->ecg_image_path)
                                <div class="mt-2">
                                    <small class="text-muted d-block mb-2">الصورة الحالية المسجلة:</small>
                                    <img src="{{ asset('storage/' . $analysis->ecg_image_path) }}" class="rounded-3 shadow-sm border" style="max-height: 120px; object-fit: contain;">
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="section-divider">
                    <i class="fa-solid fa-list-check"></i> نتائج الفحوصات الطبية الإضافية
                </div>
                <div class="row g-3 mb-5">
                    @foreach($tests as $test)
                        <div class="col-md-4">
                            <div class="p-3 rounded-4 bg-light border border-opacity-50">
                                <label class="form-label small mb-2 fw-bold text-secondary">{{ $test->name }}</label>
                                <input type="hidden" name="tests[{{ $loop->index }}][id]" value="{{ $test->id }}">
                                <input type="number" step="0.0001" name="tests[{{ $loop->index }}][value]" 
                                       value="{{ old('tests.' . $loop->index . '.value', $selectedTests[$test->id]->pivot->value ?? null) }}" 
                                       class="form-control form-control-modern bg-white" placeholder="القيمة">
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="d-flex justify-content-center gap-3 mt-5 pt-4 border-top">
                    <button type="submit" class="btn btn-update-analysis">
                        <i class="fa-solid fa-rotate me-2"></i> تحديث بيانات التحليل
                    </button>
                    <a href="{{ route('dashboard.analyses.index') }}" class="btn btn-outline-secondary" style="border-radius: 20px; padding: 15px 35px; font-weight: 700;">
                        <i class="fa-solid fa-arrow-right me-2"></i> إلغاء ورجوع
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection