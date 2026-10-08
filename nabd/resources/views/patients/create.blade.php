@extends('layouts.app')

@section('title', 'إضافة مريض جديد | نبض AI')
@section('page_title', 'تسجيل مريض جديد')

@section('content')
<style>
    /* التنسيقات الخاصة بنماذج الإدخال */
    .form-card {
        max-width: 900px;
        margin: 0 auto;
        border-radius: 30px !important;
        border: none;
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.05);
    }

    .form-label {
        font-weight: 700;
        color: #475569;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.9rem;
    }

    .form-label i {
        color: #6366f1;
        font-size: 1.1rem;
    }

    .form-control, .form-select {
        border-radius: 15px !important;
        border: 1px solid #e2e8f0;
        padding: 12px 20px;
        font-size: 1rem;
        background-color: #fcfcfc;
        transition: all 0.3s ease;
    }

    .form-control:focus, .form-select:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        background-color: #fff;
    }

    .btn-save {
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        border: none;
        padding: 12px 35px;
        border-radius: 15px;
        font-weight: 700;
        box-shadow: 0 8px 20px rgba(99, 102, 241, 0.3);
        transition: all 0.3s ease;
    }

    .btn-save:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 25px rgba(99, 102, 241, 0.4);
    }

    .btn-back {
        padding: 12px 35px;
        border-radius: 15px;
        font-weight: 600;
    }

    .form-header-icon {
        width: 60px;
        height: 60px;
        background: rgba(99, 102, 241, 0.1);
        color: #6366f1;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        margin-bottom: 20px;
    }
</style>

<div class="container py-2">
    <div class="card form-card animate__animated animate__fadeIn">
        <div class="card-body p-4 p-lg-5">
            
            <div class="text-center d-flex flex-column align-items-center mb-5">
                <div class="form-header-icon">
                    <i class="fa-solid fa-user-plus"></i>
                </div>
                <h4 class="fw-bold mb-1">بيانات المريض الأساسية</h4>
                <p class="text-muted small">يرجى إدخال البيانات بدقة لضمان دقة تحاليل الذكاء الاصطناعي</p>
            </div>

            <form method="POST" action="{{ route('dashboard.patients.store') }}">
                @csrf

                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="fa-solid fa-user-doctor"></i> المستخدم المسؤول
                        </label>
                        <select name="user_id" class="form-select form-select-lg @error('user_id') is-invalid @enderror" required>
                            <option value="" selected disabled>اختر الطبيب أو الفني المسؤول</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="fa-solid fa-id-card"></i> الاسم الكامل للمريض
                        </label>
                        <input type="text" name="full_name" value="{{ old('full_name') }}" 
                               placeholder="مثال: محمد علي أحمد" 
                               class="form-control form-control-lg @error('full_name') is-invalid @enderror" required>
                        @error('full_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="fa-solid fa-venus-mars"></i> الجنس
                        </label>
                        <div class="d-flex gap-3 mt-1">
                            <select name="gender" class="form-select form-select-lg @error('gender') is-invalid @enderror" required>
                                <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>ذكر</option>
                                <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>أنثى</option>
                            </select>
                        </div>
                        @error('gender')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="fa-solid fa-calendar-day"></i> العمر (بالسنوات)
                        </label>
                        <input type="number" name="age" value="{{ old('age') }}" 
                               placeholder="مثال: 45" 
                               class="form-control form-control-lg @error('age') is-invalid @enderror" required min="1" max="120">
                        @error('age')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mt-5 pt-3 d-flex justify-content-center gap-3">
                    <button type="submit" class="btn btn-primary btn-save">
                        <i class="fa-solid fa-check-double me-2"></i> حفظ بيانات المريض
                    </button>
                    <a href="{{ route('dashboard.patients.index') }}" class="btn btn-outline-secondary btn-back">
                        <i class="fa-solid fa-rotate-left me-2"></i> إلغاء ورجوع
                    </a>
                </div>
            </form>

        </div>
    </div>
</div>
@endsection