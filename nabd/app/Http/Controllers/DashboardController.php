<?php

namespace App\Http\Controllers;

use App\Models\Analysis;
use App\Models\Banner;
use App\Models\Patient;
use App\Models\Result;
use App\Models\User;


class DashboardController extends Controller
{
    public function index()
    {
        
        $labels = [
            'طبيعي',
            'اضطراب بنبض القلب',
            'مصاب بجلطة',
            'مصاب بجلطة سابقة',
        ];

        $usersCount = User::count();
        $patientsCount = Patient::count();
        $analysesCount = Analysis::count();
        $activeBannersCount = Banner::where('is_active', true)->count();
        $grouped = Analysis::selectRaw('TRIM(LOWER(result_text)) as label, COUNT(*) as count')
            ->groupBy('label')
            ->pluck('count', 'label');

        $resultCards = collect($labels)->map(function ($label) use ($grouped) {
            return [
                'label' => $label,
                'count' => $grouped[$label] ?? 0,
            ];
        })->values();
        $recentPatients = Patient::with('user')->latest()->limit(6)->get();
        $latestBanners = Banner::latest()->limit(4)->get();

        $analysisTrendLabels = collect(range(5, 0))->map(fn ($i) => now()->subMonths($i)->translatedFormat('M Y'))->all();
        $analysisTrendData = collect(range(5, 0))->map(fn ($i) => Analysis::whereYear('created_at', now()->subMonths($i)->year)->whereMonth('created_at', now()->subMonths($i)->month)->count())->all();

        return view('dashboard.index', compact(
            'usersCount',
            'patientsCount',
            'analysesCount',
            'activeBannersCount',
            'resultCards',
            'recentPatients',
            'latestBanners',
            'analysisTrendLabels',
            'analysisTrendData',
        ));
    }
}
