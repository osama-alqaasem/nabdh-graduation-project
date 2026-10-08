@extends('layouts.app')

@section('title', 'إضافة إعلان | نبض AI')
@section('page_title', 'إنشاء إعلان جديد')

@section('content')
<style>
    /* تنسيقات حصرية لنموذج إضافة الإعلانات */
    .create-banner-card {
        max-width: 1000px;
        margin: 0 auto;
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(15px);
        border-radius: 35px !important;
        border: 1px solid rgba(255, 255, 255, 0.4) !important;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.05) !important;
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
        border-radius: 18px !important;
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

    /* منطقة رفع الصورة */
    .upload-area {
        border: 2px dashed #cbd5e1;
        border-radius: 25px;
        padding: 40px;
        text-align: center;
        background: #f8fafc;
        transition: 0.3s;
        cursor: pointer;
        position: relative;
    }

    .upload-area:hover {
        border-color: #6366f1;
        background: #f1f5f9;
    }

    .upload-icon {
        font-size: 3rem;
        color: #6366f1;
        margin-bottom: 15px;
        display: block;
    }

    /* Switch Style for modern look */
    .form-switch .form-check-input {
        width: 3.5rem;
        height: 1.7rem;
        cursor: pointer;
    }

    .btn-save-banner {
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        border: none;
        padding: 15px 50px;
        border-radius: 20px;
        font-weight: 700;
        color: white;
        box-shadow: 0 10px 25px rgba(99, 102, 241, 0.3);
        transition: 0.3s;
    }

    .btn-save-banner:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 35px rgba(99, 102, 241, 0.4);
        color: white;
    }

    .btn-round-modern {
        border-radius: 20px;
        padding: 15px 35px;
        font-weight: 600;
        transition: 0.3s;
    }
</style>

<div class="container py-2 animate__animated animate__fadeIn">
    <div class="card create-banner-card">
        <div class="card-body p-4 p-lg-5">
            
            <div class="d-flex align-items-center gap-3 mb-5 border-bottom pb-4">
                <div class="bg-primary bg-opacity-10 p-3 rounded-4">
                    <i class="fa-solid fa-clapperboard fs-3 text-primary"></i>
                </div>
                <div>
                    <h4 class="fw-extrabold text-dark mb-0">إضافة إعلان رقمي جديد</h4>
                    <p class="text-muted small mb-0">المحتوى الذي يتم إضافته هنا سيظهر فوراً في واجهة تطبيق نبض AI</p>
                </div>
            </div>

            <form method="POST" action="{{ route('dashboard.banners.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="row g-4 text-end">
                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="fa-solid fa-heading"></i> عنوان الإعلان
                        </label>
                        <input type="text" name="title" value="{{ old('title') }}" 
                               class="form-control form-control-lg form-control-modern" 
                               placeholder="أدخل عنواناً جذاباً وملفتاً..." required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="fa-solid fa-link"></i> رابط الزر (URL)
                        </label>
                        <input type="url" name="button_url" value="{{ old('button_url') }}" 
                               class="form-control form-control-lg form-control-modern text-start" dir="ltr"
                               placeholder="https://example.com/more-info">
                    </div>

                    <div class="col-12">
                        <label class="form-label">
                            <i class="fa-solid fa-paragraph"></i> الوصف المختصر
                        </label>
                        <textarea name="description" rows="3" class="form-control form-control-lg form-control-modern" 
                                  placeholder="اشرح محتوى الإعلان للمستخدم باختصار...">{{ old('description') }}</textarea>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">
                            <i class="fa-solid fa-font"></i> النص الظاهر على الزر
                        </label>
                        <input type="text" name="button_text" value="{{ old('button_text') }}" 
                               class="form-control form-control-lg form-control-modern" 
                               placeholder="مثال: اكتشف الآن">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">
                            <i class="fa-solid fa-sort-amount-down"></i> ترتيب الظهور
                        </label>
                        <input type="number" name="sort_order" value="0" 
                               class="form-control form-control-lg form-control-modern" 
                               placeholder="0">
                    </div>

                    <div class="col-md-4 d-flex align-items-end pb-2">
                        <div class="form-check form-switch p-3 bg-light rounded-4 w-100 border d-flex justify-content-between align-items-center">
                            <label class="form-check-label fw-bold text-dark mb-0" for="active">
                                <i class="fa-solid fa-power-off text-success me-1"></i> تفعيل الإعلان فوراً
                            </label>
                            <input class="form-check-input ms-0 shadow-none" type="checkbox" name="is_active" value="1" id="active" checked>
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label">
                            <i class="fa-solid fa-cloud-arrow-up"></i> صورة الإعلان الأساسية
                        </label>
                        <div class="upload-area" onclick="document.getElementById('imageInput').click()">
                            <div id="uploadContent">
                                <i class="fa-solid fa-images upload-icon"></i>
                                <h6 class="fw-bold">انقر هنا لاختيار الصورة</h6>
                                <p class="text-muted small">يرجى استخدام صور عالية الجودة بمقاس 1200x600 بكسل لضمان أفضل ظهور</p>
                            </div>
                            <input type="file" name="image" id="imageInput" class="form-control d-none" accept="image/*" required>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-center gap-3 mt-5 pt-4 border-top">
                    <button type="submit" class="btn btn-save-banner">
                        <i class="fa-solid fa-paper-plane me-2"></i> نشر الإعلان
                    </button>
                    <a href="{{ route('dashboard.banners.index') }}" class="btn btn-outline-secondary btn-round-modern">
                        <i class="fa-solid fa-arrow-right me-2"></i> إلغاء ورجوع
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // تغيير شكل حقل الرفع عند اختيار صورة
    document.getElementById('imageInput').addEventListener('change', function(e) {
        if(e.target.files.length > 0) {
            let fileName = e.target.files[0].name;
            let uploadContent = document.getElementById('uploadContent');
            if(uploadContent) {
                uploadContent.innerHTML = `
                    <i class="fa-solid fa-circle-check upload-icon text-success"></i>
                    <h6 class="fw-bold text-success">تم اختيار الملف: ${fileName}</h6>
                    <p class="text-muted small">انقر مرة أخرى لتغيير الملف</p>
                `;
            }
        }
    });
</script>
@endsection