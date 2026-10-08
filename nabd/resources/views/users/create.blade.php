@extends('layouts.app')

@section('title', 'إضافة مستخدم جديد | نبض AI')
@section('page_title', 'إنشاء حساب مستخدم جديد')

@section('content')
<style>
    /* تنسيقات حصرية لنموذج الإضافة */
    .create-user-card {
        max-width: 900px;
        margin: 0 auto;
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(15px);
        border-radius: 35px !important;
        border: 1px solid rgba(255, 255, 255, 0.4) !important;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.05) !important;
    }

    .form-header-box {
        text-align: center;
        margin-bottom: 40px;
    }

    .header-icon-circle {
        width: 70px;
        height: 70px;
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        color: white;
        border-radius: 22px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        margin: 0 auto 15px;
        box-shadow: 0 10px 20px rgba(99, 102, 241, 0.2);
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

    .form-control-modern {
        border-radius: 16px !important;
        border: 1.5px solid #e2e8f0 !important;
        padding: 12px 20px !important;
        background-color: #f8fafc !important;
        transition: all 0.3s ease;
    }

    .form-control-modern:focus {
        border-color: #6366f1 !important;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1) !important;
        background-color: #fff !important;
    }

    .btn-save-grad {
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        border: none;
        padding: 14px 45px;
        border-radius: 18px;
        font-weight: 700;
        color: white;
        box-shadow: 0 8px 20px rgba(99, 102, 241, 0.3);
        transition: all 0.3s ease;
    }

    .btn-save-grad:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 25px rgba(99, 102, 241, 0.4);
        color: white;
    }

    .btn-back-modern {
        padding: 14px 35px;
        border-radius: 18px;
        font-weight: 600;
        border: 1px solid #e2e8f0;
        background: #fff;
        color: #64748b;
        transition: 0.3s;
    }

    .btn-back-modern:hover {
        background: #f1f5f9;
        color: #1e293b;
    }
</style>

<div class="container py-2">
    <div class="card create-user-card animate__animated animate__fadeInUp">
        <div class="card-body p-4 p-lg-5">
            
            <div class="form-header-box">
                <div class="header-icon-circle">
                    <i class="fa-solid fa-user-plus"></i>
                </div>
                <h4 class="fw-bold text-dark mb-1">إضافة مستخدم جديد</h4>
                <p class="text-muted small">قم بتعبئة البيانات أدناه لإنشاء حساب مسؤول طبي جديد في النظام</p>
            </div>

            <form method="POST" action="{{ route('dashboard.users.store') }}">
                @csrf

                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="fa-solid fa-signature"></i> الاسم الكامل
                        </label>
                        <input type="text" name="name" value="{{ old('name') }}" 
                               class="form-control form-control-lg form-control-modern @error('name') is-invalid @enderror" 
                               placeholder="أدخل اسم المستخدم الثلاثي" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="fa-solid fa-envelope"></i> البريد الإلكتروني
                        </label>
                        <input type="email" name="email" value="{{ old('email') }}" 
                               class="form-control form-control-lg form-control-modern @error('email') is-invalid @enderror" 
                               placeholder="example@mail.com" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="fa-solid fa-key"></i> كلمة المرور
                        </label>
                        <input type="password" name="password" 
                               class="form-control form-control-lg form-control-modern @error('password') is-invalid @enderror" 
                               placeholder="••••••••" required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="fa-solid fa-shield-check"></i> تأكيد كلمة المرور
                        </label>
                        <input type="password" name="password_confirmation" 
                               class="form-control form-control-lg form-control-modern" 
                               placeholder="••••••••" required>
                    </div>
                </div>

                <div class="d-flex justify-content-center gap-3 mt-5 pt-2">
                    <button type="submit" class="btn btn-save-grad shadow-sm">
                        <i class="fa-solid fa-check-double me-2"></i> إنشاء الحساب
                    </button>
                    <a href="{{ route('dashboard.users.index') }}" class="btn btn-back-modern">
                        <i class="fa-solid fa-arrow-right me-2"></i> رجوع
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection