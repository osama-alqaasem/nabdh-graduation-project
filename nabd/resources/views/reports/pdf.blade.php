<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <style>
        /* إعدادات الصفحة والخطوط */
        @page { margin: 0; }
        body { 
            direction: rtl; 
            text-align: right; 
            background-color: #ffffff; 
            margin: 0; 
            padding: 0;
            color: #1e293b;
        }

        /* الترويسة العلوية */
        .header-banner {
            background-color: #0f172a;
            color: white;
            padding: 40px 50px;
            position: relative;
        }
        .header-banner h1 { margin: 0; font-size: 28px; letter-spacing: 1px; }
        .header-banner p { margin: 5px 0 0; opacity: 0.8; font-size: 14px; }
        .report-id {
            position: absolute;
            left: 50px;
            top: 45px;
            text-align: left;
            font-size: 12px;
        }

        /* الحاوية الرئيسية */
        .content { padding: 30px 50px; }

        /* كرت التحليل */
        .analysis-container {
            border: 2px solid #f1f5f9;
            border-radius: 20px;
            margin-bottom: 40px;
            padding: 25px;
            page-break-inside: avoid;
        }

        .section-title {
            color: #6366f1;
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 15px;
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 5px;
        }

        /* شبكة البيانات */
        .info-grid { width: 100%; margin-bottom: 20px; }
        .info-grid td { padding: 8px 0; vertical-align: top; }
        .label { color: #64748b; font-size: 13px; font-weight: bold; width: 120px; }
        .value { color: #0f172a; font-size: 14px; font-weight: bold; }

        /* صناديق المؤشرات المخبرية */
        .metrics-row {
            background-color: #f8fafc;
            border-radius: 15px;
            padding: 15px;
            margin: 15px 0;
        }
        .metric-box {
            display: inline-block;
            width: 48%;
            text-align: center;
        }
        .metric-label { font-size: 12px; color: #64748b; display: block; }
        .metric-value { font-size: 18px; color: #0f172a; font-weight: bold; }

        /* شارات الحالة */
        .badge {
            padding: 6px 15px;
            border-radius: 8px;
            color: white;
            font-size: 13px;
            font-weight: bold;
            text-align: center;
        }
        .bg-danger { background-color: #ef4444; }
        .bg-warning { background-color: #f59e0b; }
        .bg-success { background-color: #10b981; }

        /* منطقة الـ ECG */
        .ecg-frame {
            margin-top: 20px;
            text-align: center;
            background-color: #1e293b;
            padding: 15px;
            border-radius: 15px;
        }
        .ecg-frame img {
            max-width: 90%;
            max-height: 250px;
            border-radius: 8px;
            border: 1px solid #334155;
        }
        .ecg-label {
            color: #94a3b8;
            font-size: 11px;
            margin-top: 8px;
        }

        /* التذييل */
        .footer {
            text-align: center;
            padding: 20px;
            font-size: 11px;
            color: #94a3b8;
            border-top: 1px solid #f1f5f9;
            margin-top: 50px;
        }
    </style>
</head>
<body>

<div class="header-banner">
    <h1>تقرير التشخيص الذكي - نبض AI</h1>
    <p>النظام المتقدم لتحليل تخطيط القلب والمؤشرات الحيوية</p>
    <div class="report-id">
        تاريخ التقرير: {{ now()->format('Y-m-d') }}<br>
        ساعة الإصدار: {{ now()->format('H:i A') }}
    </div>
</div>

<div class="content">
    @foreach($analyses as $analysis)
        @php 
            $label = $analysis->result?->label ?? 'طبيعي'; 
            $statusClass = in_array($label, ['مصاب بجلطة', 'مصاب بجلطة سابقة']) ? 'bg-danger' : ($label === 'اضطراب بنبض القلب' ? 'bg-warning' : 'bg-success');
        @endphp

        <div class="analysis-container">
            <div class="section-title">بيانات المريض والتحليل رقم #{{ $analysis->id }}</div>
            
            <table class="info-grid">
                <tr>
                    <td class="label">اسم المريض:</td>
                    <td class="value">{{ $patient->full_name ?? $analysis->patient?->full_name }}</td>
                    <td class="label">المسؤول الطبي:</td>
                    <td class="value">{{ $analysis->user?->name }}</td>
                </tr>
                <tr>
                    <td class="label">العمر:</td>
                    <td class="value">{{ $analysis->age }} عاماً</td>
                    <td class="label">التاريخ والوقت:</td>
                    <td class="value">{{ optional($analysis->created_at)->format('Y-m-d | H:i') }}</td>
                </tr>
            </table>

            <div class="section-title">المؤشرات الحيوية والمخبرية</div>
            <div class="metrics-row">
                <div class="metric-box">
                    <span class="metric-label">Troponin (T)</span>
                    <span class="metric-value">{{ $analysis->troponin ?? 'N/A' }} ng/mL</span>
                </div>
                <div class="metric-box" style="border-right: 1px solid #e2e8f0;">
                    <span class="metric-label">CK-MB</span>
                    <span class="metric-value">{{ $analysis->ckmb ?? 'N/A' }} U/L</span>
                </div>
            </div>

            <div class="section-title">التشخيص النهائي للذكاء الاصطناعي</div>
            <table class="info-grid">
                <tr>
                    <td class="label">الحالة التشخيصية:</td>
                    <td><span class="badge {{ $statusClass }}">{{ $label }}</span></td>
                    <td class="label">دقة الاحتمالية:</td>
                    <td class="value">{{ $analysis->result?->probability ?? $analysis->result_score ?? '-' }}%</td>
                </tr>
            </table>

            @if($analysis->ecgImage || $analysis->ecg_image_path)
                <div class="section-title">تخطيط القلب الرقمي (Digital ECG Scan)</div>
                <div class="ecg-frame">
                    <img src="{{ public_path('storage/' . ($analysis->ecgImage?->path ?? $analysis->ecg_image_path)) }}" alt="ECG Scan">
                    <div class="ecg-label">صورة رقمية ملتقطة عبر نظام نبض AI لموجات القلب الحيوية</div>
                </div>
            @endif
        </div>
    @endforeach

    <div class="footer">
        هذا التقرير تم إنشاؤه آلياً بواسطة نظام نبض AI للذكاء الاصطناعي.<br>
        جميع البيانات سرية ومخصصة للاستخدام الطبي الاحترافي فقط.
    </div>
</div>

</body>
</html>