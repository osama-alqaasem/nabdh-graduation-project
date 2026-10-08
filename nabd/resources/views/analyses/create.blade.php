@extends('layouts.app')

@section('title', 'إضافة تحليل | نبض AI')
@section('page_title', 'إنشاء تقرير تحليل ذكي')

@section('content')
<style>
    /* التنسيقات الحصرية لنموذج التحاليل */
    .analysis-form-card {
        max-width: 1100px;
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
        font-size: 1rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .section-divider::before {
        content: '';
        position: absolute;
        right: 0;
        height: 100%;
        width: 5px;
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
        transition: 0.3s;
    }

    .form-control-modern:focus, .form-select-modern:focus {
        border-color: #6366f1 !important;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1) !important;
        background-color: #fff !important;
    }

    /* تحسين شكل صناديق الاختيار */
    .symptom-item {
        background: #fff;
        border: 1px solid #e2e8f0;
        padding: 10px 15px;
        border-radius: 15px;
        transition: 0.3s;
        cursor: pointer;
    }

    .symptom-item:hover {
        border-color: #6366f1;
        background: rgba(99, 102, 241, 0.02);
    }

    .form-check-input:checked + .form-check-label {
        color: #6366f1;
        font-weight: 700;
    }

    .upload-box {
        border: 2px dashed #cbd5e1;
        border-radius: 20px;
        padding: 20px;
        text-align: center;
        background: #f8fafc;
        transition: 0.3s;
    }

    .upload-box:hover {
        border-color: #6366f1;
    }

    .btn-save-analysis {
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        border: none;
        padding: 15px 50px;
        border-radius: 20px;
        font-weight: 700;
        color: white;
        box-shadow: 0 10px 25px rgba(99, 102, 241, 0.3);
    }
</style>

<div class="container pb-5 animate__animated animate__fadeIn">
    <div class="card analysis-form-card">
        <div class="card-body p-4 p-lg-5">
            
            <form method="POST" action="{{ route('dashboard.analyses.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="section-divider">
                    <i class="fa-solid fa-id-card-clip"></i> المعلومات الأساسية
                </div>
                
                <div class="row g-4 mb-5">
                    <div class="col-md-4">
                        <label class="form-label"><i class="fa-solid fa-user-doctor text-primary"></i> المستخدم المسؤول</label>
                        <select name="user_id" class="form-select form-select-lg form-select-modern" required>
                            <option value="">اختر الطبيب/المحلل</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label"><i class="fa-solid fa-hospital-user text-primary"></i> المريض</label>
                        <select name="patient_id" class="form-select form-select-lg form-select-modern" required>
                            <option value="">اختر المريض من السجل</option>
                            @foreach($patients as $patient)
                                <option value="{{ $patient->id }}">{{ $patient->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label"><i class="fa-solid fa-calendar-day text-primary"></i> عمر المريض وقت الفحص</label>
                        <input type="number" name="age" class="form-control form-control-lg form-control-modern" placeholder="مثال: 45" required>
                    </div>
                </div>

                <div class="section-divider">
                    <i class="fa-solid fa-vial-virus"></i> المؤشرات الحيوية والمخبرية
                </div>

                <div class="row g-4 mb-5 text-end">
                    <div class="col-md-6">
                        <label class="form-label"><i class="fa-solid fa-droplet text-danger"></i> مستوى التروپونين (Troponin)</label>
                        <input type="number" step="0.0001" name="troponin" class="form-control form-control-lg form-control-modern" placeholder="0.0000">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"><i class="fa-solid fa-microscope text-info"></i> مستوى CK-MB</label>
                        <input type="number" step="0.0001" name="ckmb" class="form-control form-control-lg form-control-modern" placeholder="0.0000">
                    </div>
                </div>

                <div class="section-divider">
                    <i class="fa-solid fa-brain"></i> مخرجات نظام نـبـض AI
                </div>

                <div class="p-4 rounded-4 bg-light bg-opacity-50 border mb-5 shadow-sm">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label"><i class="fa-solid fa-comment-medical text-primary"></i> التشخيص النصي</label>
                            <input type="text" name="result_text" class="form-control form-control-lg form-control-modern" placeholder="طبيعي / اضطراب / جلطة...">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label"><i class="fa-solid fa-percent text-primary"></i> النسبة (Score)</label>
                            <input type="number" step="0.01" name="result_score" class="form-control form-control-lg form-control-modern" placeholder="0.00">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label"><i class="fa-solid fa-chart-line text-primary"></i> الاحتمالية (Probability)</label>
                            <input type="number" step="0.01" name="probability" class="form-control form-control-lg form-control-modern" placeholder="0.00">
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label small text-muted">Image Analysis Score</label>
                            <input type="number" step="0.01" name="image_score" class="form-control form-control-modern">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted">Tabular Data Score</label>
                            <input type="number" step="0.01" name="tabular_score" class="form-control form-control-modern">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted">Symptoms Weight Score</label>
                            <input type="number" step="0.01" name="symptoms_score" class="form-control form-control-modern">
                        </div>
                    </div>
                </div>

                <div class="row g-4 mb-5">
                    <div class="col-lg-6">
                        <div class="section-divider"><i class="fa-solid fa-stethoscope"></i> الأعراض المرصودة</div>
                        <div class="p-3 rounded-4 bg-white border h-100 overflow-auto" style="max-height: 300px;">
                            <div class="row g-2">
                                @foreach($symptoms as $symptom)
                                    <div class="col-md-6">
                                        <div class="symptom-item">
                                            <div class="form-check mb-0">
                                                <input class="form-check-input" type="checkbox" name="symptoms[]" value="{{ $symptom->id }}" id="symp{{ $symptom->id }}">
                                                <label class="form-check-label small" for="symp{{ $symptom->id }}">{{ $symptom->name }}</label>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="section-divider"><i class="fa-solid fa-file-waveform"></i> فحص ECG المرئي</div>
                        <div class="upload-box h-100 d-flex flex-column justify-content-center align-items-center">
                            <i class="fa-solid fa-cloud-arrow-up fs-1 text-primary mb-3"></i>
                            <h6 class="fw-bold mb-2">صورة تخطيط القلب</h6>
                            <p class="text-muted small mb-3">اسحب الصورة هنا أو اختر الملف</p>
                            <input type="file" name="ecg_image" class="form-control form-control-modern" accept="image/*">
                        </div>
                    </div>
                </div>

                <div class="section-divider">
                    <i class="fa-solid fa-list-check"></i> فحوصات طبية إضافية
                </div>
                <div class="row g-3 mb-5">
                    @foreach($tests as $test)
                        <div class="col-md-4">
                            <div class="p-3 rounded-4 bg-light border border-opacity-50">
                                <label class="form-label small mb-2 fw-bold text-secondary">{{ $test->name }}</label>
                                <input type="hidden" name="tests[{{ $loop->index }}][id]" value="{{ $test->id }}">
                                <input type="number" step="0.0001" name="tests[{{ $loop->index }}][value]" class="form-control form-control-modern bg-white" placeholder="القيمة">
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="d-flex justify-content-center gap-3 mt-5 pt-4 border-top">
                    <button type="submit" class="btn btn-save-analysis shadow-lg">
                        <i class="fa-solid fa-paper-plane me-2"></i> اعتماد وحفظ التحليل
                    </button>
                    <a href="{{ route('dashboard.analyses.index') }}" class="btn btn-outline-secondary" style="border-radius: 20px; padding: 15px 35px; font-weight: 700;">
                        <i class="fa-solid fa-arrow-right me-2"></i> رجوع للقائمة
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection