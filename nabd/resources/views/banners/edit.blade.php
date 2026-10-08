@extends('layouts.app')

@section('title', 'تعديل إعلان | نبض AI')
@section('page_title', 'تعديل محتوى الإعلان')

@section('content')
<style>
    /* تنسيقات حصرية لنموذج تعديل الإعلانات */
    .edit-banner-card {
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

    /* منطقة معاينة الصورة */
    .preview-container {
        position: relative;
        border-radius: 25px;
        overflow: hidden;
        border: 2px dashed #e2e8f0;
        padding: 10px;
        background: #fff;
        margin-top: 15px;
        transition: 0.3s;
    }

    .preview-container:hover {
        border-color: #6366f1;
    }

    .img-preview-modern {
        width: 100%;
        max-height: 300px;
        object-fit: cover;
        border-radius: 20px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.05);
    }

    .image-overlay-label {
        position: absolute;
        top: 20px;
        right: 20px;
        background: rgba(15, 23, 42, 0.7);
        color: #fff;
        padding: 5px 15px;
        border-radius: 12px;
        font-size: 0.75rem;
        backdrop-filter: blur(5px);
    }

    /* Switch Style for Active Status */
    .form-switch .form-check-input {
        width: 3rem;
        height: 1.5rem;
        cursor: pointer;
    }

    .btn-update-banner {
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        border: none;
        padding: 14px 45px;
        border-radius: 20px;
        font-weight: 700;
        color: white;
        box-shadow: 0 10px 25px rgba(99, 102, 241, 0.3);
        transition: 0.3s;
    }

    .btn-update-banner:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 35px rgba(99, 102, 241, 0.4);
        color: white;
    }

    .btn-round-modern {
        border-radius: 20px;
        padding: 14px 35px;
        font-weight: 600;
        transition: 0.3s;
    }
</style>

<div class="container py-2 animate__animated animate__fadeIn">
    <div class="card edit-banner-card">
        <div class="card-body p-4 p-lg-5">
            
            <div class="d-flex align-items-center gap-3 mb-5 border-bottom pb-4">
                <div class="bg-primary bg-opacity-10 p-3 rounded-4">
                    <i class="fa-solid fa-pen-to-square fs-3 text-primary"></i>
                </div>
                <div>
                    <h4 class="fw-extrabold text-dark mb-0">تعديل الإعلان الرقمي</h4>
                    <p class="text-muted small mb-0">قم بتحديث المحتوى المرئي والروابط الموجهة لتطبيق الهاتف</p>
                </div>
            </div>

            <form method="POST" action="{{ route('dashboard.banners.update', $banner) }}" enctype="multipart/form-data">
                @csrf 
                @method('PUT')

                <div class="row g-4 text-end">
                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="fa-solid fa-heading"></i> عنوان الإعلان الرئيسي
                        </label>
                        <input type="text" name="title" value="{{ old('title', $banner->title) }}" 
                               class="form-control form-control-lg form-control-modern" 
                               placeholder="أدخل عنواناً جذاباً..." required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="fa-solid fa-link"></i> رابط الإجراء (URL)
                        </label>
                        <input type="url" name="button_url" value="{{ old('button_url', $banner->button_url) }}" 
                               class="form-control form-control-lg form-control-modern text-start" dir="ltr"
                               placeholder="https://example.com/promo">
                    </div>

                    <div class="col-12">
                        <label class="form-label">
                            <i class="fa-solid fa-align-right"></i> وصف الإعلان التفصيلي
                        </label>
                        <textarea name="description" rows="3" class="form-control form-control-lg form-control-modern" 
                                  placeholder="اكتب وصفاً مختصراً يظهر للمستخدمين...">{{ old('description', $banner->description) }}</textarea>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">
                            <i class="fa-solid fa-mouse-pointer"></i> نص زر الإجراء
                        </label>
                        <input type="text" name="button_text" value="{{ old('button_text', $banner->button_text) }}" 
                               class="form-control form-control-lg form-control-modern" 
                               placeholder="مثال: اشترك الآن">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">
                            <i class="fa-solid fa-sort-numeric-down"></i> ترتيب الظهور في التطبيق
                        </label>
                        <input type="number" name="sort_order" value="{{ old('sort_order', $banner->sort_order) }}" 
                               class="form-control form-control-lg form-control-modern" 
                               placeholder="0">
                    </div>

                    <div class="col-md-4 d-flex align-items-end pb-2">
                        <div class="form-check form-switch p-3 bg-light rounded-4 w-100 border text-end d-flex justify-content-between align-items-center">
                            <label class="form-check-label fw-bold text-dark mb-0" for="active">
                                <i class="fa-solid fa-circle-check text-success me-1"></i> إعلان فعال حالياً
                            </label>
                            <input class="form-check-input ms-0 shadow-none" type="checkbox" name="is_active" value="1" id="active" @checked($banner->is_active)>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="fa-solid fa-image"></i> تحديث صورة الإعلان (اختياري)
                        </label>
                        <input type="file" name="image" class="form-control form-control-lg form-control-modern" accept="image/*">
                        <small class="text-muted mt-2 d-block"><i class="fa-solid fa-info-circle me-1"></i> المقاس الموصى به: 1200x600 بكسل</small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="fa-solid fa-eye"></i> معاينة الصورة الحالية
                        </label>
                        <div class="preview-container">
                            <span class="image-overlay-label">الصورة الحالية</span>
                            <img src="{{ asset('storage/' . $banner->image_path) }}" class="img-preview-modern" alt="Banner Preview">
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-center gap-3 mt-5 pt-4 border-top">
                    <button type="submit" class="btn btn-update-banner">
                        <i class="fa-solid fa-check-double me-2"></i> تحديث البيانات
                    </button>
                    <a href="{{ route('dashboard.banners.index') }}" class="btn btn-outline-secondary btn-round-modern">
                        <i class="fa-solid fa-arrow-right me-2"></i> إلغاء ورجوع
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection