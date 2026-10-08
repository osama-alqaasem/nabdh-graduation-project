@extends('layouts.app')
@section('title', 'تفاصيل المستخدم | نبض AI')
@section('page_title', 'الملف الشخصي للمسؤول الطبي')

@section('content')
<style>
    /* التنسيقات الحصرية للداشبورد */
    .exclusive-card {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(15px);
        border-radius: 35px !important;
        border: 1px solid rgba(255, 255, 255, 0.4) !important;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.05) !important;
        transition: 0.4s ease-in-out;
    }

    .exclusive-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 30px 60px rgba(99, 102, 241, 0.1) !important;
    }

    .avatar-glow {
        width: 110px;
        height: 110px;
        background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
        border-radius: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3rem;
        color: white;
        font-weight: 800;
        margin: 0 auto 20px;
        box-shadow: 0 15px 30px rgba(99, 102, 241, 0.4);
        position: relative;
    }

    .avatar-glow::after {
        content: '👨‍⚕️';
        position: absolute;
        bottom: -5px;
        right: -5px;
        font-size: 1.5rem;
        background: white;
        padding: 5px;
        border-radius: 12px;
        box-shadow: 0 5px 10px rgba(0,0,0,0.1);
    }

    .stat-pill {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        padding: 12px 20px;
        border-radius: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 700;
        color: #1e293b;
        transition: 0.3s;
    }

    .stat-pill:hover {
        background: #6366f1;
        color: white;
        border-color: #6366f1;
    }

    .modern-table thead th {
        background: #f1f5f9;
        border: none;
        padding: 18px;
        color: #64748b;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 1px;
    }

    .modern-table tbody tr {
        border-bottom: 1px solid #f1f5f9;
        transition: 0.3s;
    }

    .modern-table tbody tr:hover {
        background: rgba(99, 102, 241, 0.02);
    }

    .btn-action-pill {
        border-radius: 14px;
        padding: 8px 25px;
        font-weight: 700;
        box-shadow: 0 5px 15px rgba(99, 102, 241, 0.2);
    }

    .section-sticker {
        background: #daffde;
        color: #166534;
        padding: 5px 15px;
        border-radius: 50px;
        font-size: 0.75rem;
        font-weight: 800;
        margin-right: 10px;
    }
</style>

<div class="row g-4 mb-4">
    <div class="col-lg-4">
        <div class="card exclusive-card h-100 animate__animated animate__fadeInRight">
            <div class="card-body text-center p-5">
                <div class="avatar-glow">
                    {{ mb_substr($user->name, 0, 1) }}
                </div>
                <h3 class="fw-bold text-dark mb-1">{{ $user->name }}</h3>
                <div class="text-muted mb-4 small"><i class="fa-solid fa-envelope me-1"></i> {{ $user->email }}</div>
                
                <hr class="opacity-10 my-4">

                <div class="d-flex flex-column gap-3">
                    <div class="stat-pill">
                        <span class="fs-4">👥</span>
                        <div class="text-start">
                            <small class="text-muted d-block" style="font-size: 0.7rem">إجمالي المرضى</small>
                            <span>{{ $user->patients->count() }} مريض</span>
                        </div>
                    </div>
                    <div class="stat-pill">
                        <span class="fs-4">🧪</span>
                        <div class="text-start">
                            <small class="text-muted d-block" style="font-size: 0.7rem">التحاليل المنجزة</small>
                            <span>{{ $user->analyses->count() }} تحليل ذكي</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card exclusive-card h-100 animate__animated animate__fadeInLeft">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold mb-0"><i class="fa-solid fa-users-viewfinder text-primary me-2"></i> سجل المرضى الملحقين</h5>
                    <span class="section-sticker">تحديث فوري ⚡</span>
                </div>

                <div class="table-responsive">
                    <table class="table modern-table align-middle">
                        <thead>
                            <tr>
                                <th class="rounded-start-4">اسم المريض</th>
                                <th>العمر</th>
                                <th>الجنس</th>
                                <th class="text-center rounded-end-4">الإجراء</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($user->patients as $patient)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width:40px; height:40px">
                                            {{ mb_substr($patient->full_name, 0, 1) }}
                                        </div>
                                        <div class="fw-bold text-dark">{{ $patient->full_name }}</div>
                                    </div>
                                </td>
                                <td><span class="badge bg-light text-dark border p-2 rounded-3">{{ $patient->age }} سنة</span></td>
                                <td>
                                    @if($patient->gender === 'male' || $patient->gender === 'ذكر')
                                        <span class="text-primary"><i class="fa-solid fa-mars me-1"></i> ذكر</span>
                                    @else
                                        <span class="text-danger"><i class="fa-solid fa-venus me-1"></i> أنثى</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a class="btn btn-primary btn-action-pill shadow-sm" href="{{ route('dashboard.patients.show', $patient) }}">
                                        عرض السجل <i class="fa-solid fa-arrow-left ms-2"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="fa-solid fa-inbox fs-1 d-block mb-3 opacity-20"></i>
                                        لا توجد بيانات متاحة لهذا المستخدم حالياً
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

