@extends('layouts.app')

@section('title', 'تعديل بيانات مريض | نبض AI')
@section('page_title', 'تعديل ملف المريض')

@section('content')
<style>
    /* تنسيقات حصرية لصفحة التعديل */
    .edit-form-card {
        max-width: 900px;
        margin: 0 auto;
        border-radius: 30px !important;
        border: none;
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(12px);
        box-shadow: 0 15px 45px rgba(0, 0, 0, 0.05);
    }

    .header-edit-icon {
        width: 65px;
        height: 65px;
        background: rgba(99, 102, 241, 0.1);
        color: #6366f1;
        border-radius: 22px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        margin-bottom: 20px;
        box-shadow: 0 8px 15px rgba(99, 102, 241, 0.05);
    }

    .form-label {
        font-weight: 700;
        color: #334155;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.85rem;
    }

    .form-label i {
        color: #6366f1;
        font-size: 1rem;
    }

    .form-control-custom, .form-select-custom {
        border-radius: 16px !important;
        border: 1px solid #e2e8f0;
        padding: 12px 18px;
        background-color: #f8fafc;
        transition: all 0.3s ease;
    }

    .form-control-custom:focus, .form-select-custom:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        background-color: #fff;
    }

    .btn-update {
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        border: none;
        padding: 12px 40px;
        border-radius: 16px;
        font-weight: 700;
        color: white;
        box-shadow: 0 10px 20px rgba(99, 102, 241, 0.25);
        transition: all 0.3s ease;
    }

    .btn-update:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 30px rgba(99, 102, 241, 0.35);
        color: white;
    }

    .btn-back-custom {
        padding: 12px 30px;
        border-radius: 16px;
        font-weight: 600;
        border: 1px solid #e2e8f0;
        background: #fff;
        color: #64748b;
    }
</style>

<div class="container py-3">
    <div class="card edit-form-card animate__animated animate__fadeInUp">
        <div class="card-body p-4 p-lg-5">
            
            <div class="d-flex flex-column align-items-center text-center mb-5">
                <div class="header-edit-icon">
                    <i class="fa-solid fa-user-pen"></i>
                </div>
                <h4 class="fw-bold mb-1 text-dark">تعديل ملف: {{ $patient->full_name }}</h4>
                <p class="text-muted small">يمكنك تحديث البيانات الأساسية للمريض وتعديل المسؤول عن الحالة</p>
            </div>

            <form method="POST" action="{{ route('dashboard.patients.update', $patient) }}">
                @csrf 
                @method('PUT')

                <div class="row g-4 text-end">
                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="fa-solid fa-user-doctor"></i> المستخدم المسؤول
                        </label>
                        <select name="user_id" class="form-select form-select-lg form-select-custom @error('user_id') is-invalid @enderror" required>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" @selected(old('user_id', $patient->user_id) == $user->id)>
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
                            <i class="fa-solid fa-signature"></i> الاسم الكامل للمريض
                        </label>
                        <input type="text" name="full_name" value="{{ old('full_name', $patient->full_name) }}" 
                               class="form-control form-control-lg form-control-custom @error('full_name') is-invalid @enderror" required>
                        @error('full_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="fa-solid fa-venus-mars"></i> الجنس
                        </label>
                        <select name="gender" class="form-select form-select-lg form-select-custom @error('gender') is-invalid @enderror" required>
                            <option value="male" @selected(old('gender', $patient->gender) === 'male')>ذكر</option>
                            <option value="female" @selected(old('gender', $patient->gender) === 'female')>أنثى</option>
                            <option value="other" @selected(old('gender', $patient->gender) === 'other')>غير محدد</option>
                        </select>
                        @error('gender')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="fa-solid fa-id-card-clip"></i> العمر الحالي
                        </label>
                        <input type="number" name="age" value="{{ old('age', $patient->age) }}" 
                               class="form-control form-control-lg form-control-custom @error('age') is-invalid @enderror" required min="1">
                        @error('age')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="d-flex justify-content-center gap-3 mt-5">
                    <button type="submit" class="btn btn-update shadow-sm">
                        <i class="fa-solid fa-rotate me-2"></i> تحديث البيانات
                    </button>
                    <a href="{{ route('dashboard.patients.index') }}" class="btn btn-back-custom">
                        <i class="fa-solid fa-xmark me-2"></i> إلغاء ورجوع
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection