<?php

namespace App\Http\Controllers;

use App\Models\Analysis;
use App\Models\EcgImage;
use App\Models\Patient;
use App\Models\Symptom;
use App\Models\Test;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AnalysisController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q'));
        $userId = $request->get('user_id');
        $patientId = $request->get('patient_id');

        $analyses = Analysis::with(['user','patient','result'])
            ->whereIn('id', function($query) {
                $query->selectRaw('MAX(id)')
                      ->from('analyses')
                      ->groupBy('patient_id');
            })
            ->when($q, fn ($query) => $query->whereHas('patient', fn ($sub) => $sub->where('full_name', 'like', "%{$q}%")))
            ->when($userId, fn ($query) => $query->where('user_id', $userId))
            ->when($patientId, fn ($query) => $query->where('patient_id', $patientId))
            ->latest()->paginate(15)->withQueryString();

        $users = User::orderBy('name')->get(['id','name']);
        $patients = Patient::orderBy('full_name')->get(['id','full_name']);
        return view('analyses.index', compact('analyses','users','patients','q','userId','patientId'));
    }

    public function create()
    {
        $users = User::orderBy('name')->get(['id','name']);
        $patients = Patient::orderBy('full_name')->get(['id','full_name']);
        $symptoms = Symptom::orderBy('name')->get(['id','name']);
        $tests = Test::orderBy('name')->get(['id','name']);
        return view('analyses.create', compact('users','patients','symptoms','tests'));
    }

  public function store(Request $request)
{
$data = $request->validate([
    'patient_id' => ['required','exists:patients,id'],
    'age' => ['required','integer','min:0','max:120'],
    'troponin' => ['nullable','numeric'],
    'ckmb' => ['nullable','numeric'],
    'result_score' => ['nullable','numeric'],
    'result_text' => ['nullable','string','max:255'],
    'file' => ['nullable','image','max:4096'],
    'symptoms' => ['nullable','array'],
    'tests' => ['nullable','array'],
    'probability' => ['nullable','numeric'],
    'label' => ['nullable','string','max:255'],
    'image_score' => ['nullable','numeric'],
    'tabular_score' => ['nullable','numeric'],
    'symptoms_score' => ['nullable','numeric'],
]);

$analysis = Analysis::create([
    'user_id' => $request->user()->id,
    'patient_id' => $data['patient_id'],
    'age' => $data['age'],
    'troponin' => $data['troponin'] ?? null,
    'ckmb' => $data['ckmb'] ?? null,
    'result_score' => $data['result_score'] ?? null,
    'result_text' => $data['result_text'] ?? null,
]);

if ($request->hasFile('file')) {
    $path = $request->file('file')->store('analyses', 'public');

    $analysis->update([
        'ecg_image_path' => $path,
    ]);

    EcgImage::updateOrCreate(
        ['analysis_id' => $analysis->id],
        ['path' => $path]
    );
}

$s = $request->input('symptoms', []);
$symptoms = [];

if (($s['chest_pain'] ?? 0) == 1) {
    $id = Symptom::where('name', 'chest_pain')->value('id');
    if ($id) $symptoms[$id] = ['value' => 1];
}
if (($s['shortness'] ?? 0) == 1) {
    $id = Symptom::where('name', 'shortness')->value('id');
    if ($id) $symptoms[$id] = ['value' => 1];
}
if (($s['nausea'] ?? 0) == 1) {
    $id = Symptom::where('name', 'nausea')->value('id');
    if ($id) $symptoms[$id] = ['value' => 1];
}
if (($s['dizziness'] ?? 0) == 1) {
    $id = Symptom::where('name', 'dizziness')->value('id');
    if ($id) $symptoms[$id] = ['value' => 1];
}
if (($s['sweating'] ?? 0) == 1) {
    $id = Symptom::where('name', 'sweating')->value('id');
    if ($id) $symptoms[$id] = ['value' => 1];
}
if (($s['fatigue'] ?? 0) == 1) {
    $id = Symptom::where('name', 'fatigue')->value('id');
    if ($id) $symptoms[$id] = ['value' => 1];
}

$analysis->symptoms()->sync($symptoms);

    $analysis->tests()->sync(
        collect($data['tests'] ?? [])
            ->filter(fn ($row) => !empty($row['id']))
            ->mapWithKeys(fn ($row) => [$row['id'] => ['value' => $row['value'] ?? null]])
            ->all()
    );

    $analysis->result()->updateOrCreate(
        ['analysis_id' => $analysis->id],
        [
            'probability' => $data['probability'] ?? $data['result_score'] ?? null,
            'label' => $this->normalizeLabel($data['label'] ?? ($data['result_text'] ?? 'طبيعي')),
            'image_score' => $data['image_score'] ?? null,
            'tabular_score' => $data['tabular_score'] ?? null,
            'symptoms_score' => $data['symptoms_score'] ?? null,
        ]
    );

    return redirect()->route('dashboard.analyses.index')->with('success', 'تمت إضافة التحليل.');
}

    public function show(Analysis $analysis)
    {
        $analysis->load(['user','patient','symptoms','tests','ecgImage','result']);
        return view('analyses.show', compact('analysis'));
    }

    public function edit(Analysis $analysis)
    {
        $analysis->load(['symptoms','tests','result','ecgImage']);
        $users = User::orderBy('name')->get(['id','name']);
        $patients = Patient::orderBy('full_name')->get(['id','full_name']);
        $symptoms = Symptom::orderBy('name')->get(['id','name']);
        $tests = Test::orderBy('name')->get(['id','name']);
        return view('analyses.edit', compact('analysis','users','patients','symptoms','tests'));
    }

    public function update(Request $request, Analysis $analysis)
    {
        $data = $request->validate([
            'user_id' => ['required','exists:users,id'],
            'patient_id' => ['required','exists:patients,id'],
            'age' => ['required','integer','min:0','max:120'],
            'troponin' => ['nullable','numeric'],
            'ckmb' => ['nullable','numeric'],
            'result_score' => ['nullable','numeric'],
            'result_text' => ['nullable','string','max:255'],
            'file' => ['nullable','image','max:4096'],
            'symptoms' => ['array'],
            'symptoms.*' => ['integer','exists:symptoms,id'],
            'tests' => ['array'],
            'tests.*.id' => ['nullable','integer','exists:tests,id'],
            'tests.*.value' => ['nullable','numeric'],
            'probability' => ['nullable','numeric'],
            'label' => ['nullable','string','max:255'],
            'image_score' => ['nullable','numeric'],
            'tabular_score' => ['nullable','numeric'],
            'symptoms_score' => ['nullable','numeric'],
        ]);

        $analysis->update([
            'user_id' => $data['user_id'],
            'patient_id' => $data['patient_id'],
            'age' => $data['age'],
            'troponin' => $data['troponin'] ?? null,
            'ckmb' => $data['ckmb'] ?? null,
            'result_score' => $data['result_score'] ?? null,
            'result_text' => $data['result_text'] ?? null,
        ]);

if ($request->hasFile('file')) {
    if ($analysis->ecgImage?->path) {
        Storage::disk('public')->delete($analysis->ecgImage->path);
    }

    $path = $request->file('file')->store('analyses', 'public');

    $analysis->update(['ecg_image_path' => $path]);

    EcgImage::updateOrCreate(
        ['analysis_id' => $analysis->id],
        ['path' => $path]
    );
}
    $s = $request->input('symptoms', []);
$symptoms = [];

if (($s['chest_pain'] ?? 0) == 1) {
    $id = Symptom::where('name', 'chest_pain')->value('id');
    if ($id) $symptoms[$id] = ['value' => 1];
}
if (($s['shortness'] ?? 0) == 1) {
    $id = Symptom::where('name', 'shortness')->value('id');
    if ($id) $symptoms[$id] = ['value' => 1];
}
if (($s['nausea'] ?? 0) == 1) {
    $id = Symptom::where('name', 'nausea')->value('id');
    if ($id) $symptoms[$id] = ['value' => 1];
}
if (($s['dizziness'] ?? 0) == 1) {
    $id = Symptom::where('name', 'dizziness')->value('id');
    if ($id) $symptoms[$id] = ['value' => 1];
}
if (($s['sweating'] ?? 0) == 1) {
    $id = Symptom::where('name', 'sweating')->value('id');
    if ($id) $symptoms[$id] = ['value' => 1];
}
if (($s['fatigue'] ?? 0) == 1) {
    $id = Symptom::where('name', 'fatigue')->value('id');
    if ($id) $symptoms[$id] = ['value' => 1];
}

$analysis->symptoms()->sync($symptoms);


        $analysis->tests()->sync(collect($data['tests'] ?? [])->filter(fn ($row) => !empty($row['id']))->mapWithKeys(fn ($row) => [$row['id'] => ['value' => $row['value'] ?? null]])->all());

        $analysis->result()->updateOrCreate(
            ['analysis_id' => $analysis->id],
            [
                'probability' => $data['probability'] ?? $data['result_score'] ?? null,
                'label' => $this->normalizeLabel($data['label'] ?? ($data['result_text'] ?? 'طبيعي')),
                'image_score' => $data['image_score'] ?? null,
                'tabular_score' => $data['tabular_score'] ?? null,
                'symptoms_score' => $data['symptoms_score'] ?? null,
            ]
        );

        return redirect()->route('dashboard.analyses.index')->with('success', 'تم تحديث التحليل.');
    }

    public function destroy(Analysis $analysis)
    {
        if ($analysis->ecgImage?->path) Storage::disk('public')->delete($analysis->ecgImage->path);
        $analysis->delete();
        return redirect()->route('dashboard.analyses.index')->with('success', 'تم حذف التحليل.');
    }

    private function normalizeLabel(?string $label): string
    {
        $normalized = trim((string) $label);
        if ($normalized === '') {
            return 'طبيعي';
        }

        if (in_array($normalized, ['طبيعي', 'اضطراب بنبض القلب', 'مصاب بجلطة', 'مصاب بجلطة سابقة'], true)) {
            return $normalized;
        }

        $lower = str_replace(['_', '-'], ' ', mb_strtolower($normalized));

        return match (true) {
            str_contains($lower, 'arrhythm'),
            str_contains($lower, 'اضطراب'),
            str_contains($lower, 'نبض') => 'اضطراب بنبض القلب',
            str_contains($lower, 'previous'),
            str_contains($lower, 'old'),
            str_contains($lower, 'سابق') => 'مصاب بجلطة سابقة',
            str_contains($lower, 'positive'),
            str_contains($lower, 'stroke'),
            str_contains($lower, 'heart attack'),
            str_contains($lower, 'جلطة') => 'مصاب بجلطة',
            str_contains($lower, 'normal'),
            str_contains($lower, 'negative'),
            str_contains($lower, 'طبيعي') => 'طبيعي',
            default => 'طبيعي',
        };
    }

}
