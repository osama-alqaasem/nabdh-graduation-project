<?php

namespace App\Http\Controllers;

use App\Models\Analysis;
use App\Models\Patient;
use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf as Pdf;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $label = $request->get('label');
        $patientId = $request->get('patient_id');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        $analyses = Analysis::with(['patient', 'user', 'result'])
            ->when($label, fn ($q) => $q->whereHas('result', fn ($sub) => $sub->where('label', $label)))
            ->when($patientId, fn ($q) => $q->where('patient_id', $patientId))
            ->when($dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->whereDate('created_at', '<=', $dateTo))
            ->latest()->paginate(20)->withQueryString();

        $patients = Patient::orderBy('full_name')->get(['id', 'full_name']);
        return view('reports.index', compact('analyses', 'patients', 'label', 'patientId', 'dateFrom', 'dateTo'));
    }

    public function pdf(Request $request)
    {
        $label = $request->get('label');
        $patientId = $request->get('patient_id');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        $analyses = Analysis::with(['patient', 'user', 'result', 'symptoms', 'tests', 'ecgImage'])
            ->when($label, fn ($q) => $q->whereHas('result', fn ($sub) => $sub->where('label', $label)))
            ->when($patientId, fn ($q) => $q->where('patient_id', $patientId))
            ->when($dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->whereDate('created_at', '<=', $dateTo))
            ->latest()->get();

        if (!class_exists(Pdf::class)) {
            return back()->with('error', 'حزمة PDF غير مثبتة. تأكد من تثبيت mccarlosen/laravel-mpdf.');
        }

        return Pdf::loadView('reports.pdf', compact('analyses'))
            ->download('heart-report-' . now()->format('Y-m-d_His') . '.pdf');
    }
}
