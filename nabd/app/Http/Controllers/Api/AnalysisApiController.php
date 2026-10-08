<?php

namespace App\Http\Controllers\Api;

use App\Models\Analysis;
use App\Models\EcgImage;
use App\Models\Patient;
use App\Models\Result;
use App\Models\Symptom;
use App\Models\Test;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AnalysisApiController extends ApiController
{
    public function index(Request $request)
{
    $user = $this->requireUser($request);
return response()->json(
    Analysis::with(['patient', 'result', 'symptoms', 'tests', 'ecgImage'])
        ->where('user_id', $user->id)
        ->latest()
        ->get()
);
}

    public function history(Request $request)
    {
        return $this->index($request);
    }
public function store(Request $request)
{
    $user = $this->requireUser($request);

    $data = $request->validate([
        'patient_id' => ['nullable'],
        'full_name' => ['nullable', 'string', 'max:255'],
        'gender' => ['nullable', 'string', 'max:20'],
        'age' => ['nullable'],
        'troponin' => ['nullable'],
        'ckmb' => ['nullable'],
        'result_score' => ['nullable'],
        'result_text' => ['nullable', 'string', 'max:255'],
        'ecg_image_path' => ['nullable', 'string', 'max:255'],
        'symptoms' => ['nullable'],
        'tests' => ['nullable'],
        'raw_result' => ['nullable'],
        'probability' => ['nullable'],
        'label' => ['nullable', 'string', 'max:255'],
        'image_score' => ['nullable'],
        'tabular_score' => ['nullable'],
        'symptoms_score' => ['nullable'],
    ]);

    $testsInput = $request->input('tests', []);
    $rawResultInput = $request->input('raw_result');

    $symptomsInput = $request->input('symptoms', []);
$symptoms = is_string($symptomsInput)
    ? (json_decode($symptomsInput, true) ?? [])
    : (is_array($symptomsInput) ? $symptomsInput : []);

    $tests = is_string($testsInput) ? (json_decode($testsInput, true) ?? []) : (is_array($testsInput) ? $testsInput : []);
    $rawResult = is_string($rawResultInput) ? (json_decode($rawResultInput, true) ?? $rawResultInput) : $rawResultInput;

    $patient = $this->resolvePatient($user->id, $data);

    $analysis = Analysis::create([
        'user_id' => $user->id,
        'patient_id' => $patient->id,
        'age' => $data['age'] ?? $patient->age,
        'troponin' => $this->parseNullableFloat($data['troponin'] ?? null),
        'ckmb' => $this->parseNullableFloat($data['ckmb'] ?? null),
        'result_score' => $this->parseNullableFloat($data['result_score'] ?? $data['probability'] ?? null),
        'result_text' => $data['result_text'] ?? null,
        'ecg_image_path' => $data['ecg_image_path'] ?? null,
    ]);

    if ($request->hasFile('file')) {
        $file = $request->file('file');
        $path = $file->store('ecg-images', 'public');

        $analysis->update([
            'ecg_image_path' => $path,
        ]);

        EcgImage::updateOrCreate(
            ['analysis_id' => $analysis->id],
            ['path' => $path]
        );
    }

$result = new Result();
$result->analysis_id = $analysis->id;
$result->probability = $this->parseNullableFloat($data['probability'] ?? $data['result_score'] ?? null);
$result->label = $this->normalizeLabel(
    $data['label'] ?? $data['result_text'] ?? ($rawResult['label'] ?? 'طبيعي')
);
$result->image_score = $this->parseNullableFloat($data['image_score'] ?? null);
$result->tabular_score = $this->parseNullableFloat($data['tabular_score'] ?? null);
$result->symptoms_score = $this->parseNullableFloat($data['symptoms_score'] ?? null);
$result->save();

$analysis->symptoms()->sync($this->resolveSymptoms($symptoms));
$analysis->tests()->sync($this->resolveTests($tests));

    return response()->json([
        'message' => 'Analysis created',
        'data' => $this->transform($analysis->load(['patient', 'result', 'symptoms', 'tests', 'ecgImage'])),
    ], 201);
}
    public function show(Request $request, Analysis $analysis)
    {
        $user = $this->requireUser($request);
        abort_unless($analysis->user_id === $user->id, 403);

        return response()->json(['data' => $this->transform($analysis->load(['patient', 'result', 'symptoms', 'tests', 'ecgImage']))]);
    }

    public function update(Request $request, Analysis $analysis)
    {
        $user = $this->requireUser($request);
        abort_unless($analysis->user_id === $user->id, 403);

        $data = $request->validate([
            'patient_id' => ['nullable', 'integer', 'exists:patients,id'],
            'full_name' => ['nullable', 'string', 'max:255'],
            'gender' => ['nullable', 'string', 'max:20'],
            'age' => ['nullable', 'integer', 'min:0', 'max:120'],
            'troponin' => ['nullable', 'numeric'],
            'ckmb' => ['nullable', 'numeric'],
            'result_score' => ['nullable', 'numeric'],
            'result_text' => ['nullable', 'string', 'max:255'],
            'ecg_image_path' => ['nullable', 'string', 'max:255'],
            'symptoms' => ['nullable'],
            'tests' => ['nullable'],
            'raw_result' => ['nullable'],
            'probability' => ['nullable', 'numeric'],
            'label' => ['nullable', 'string', 'max:255'],
            'image_score' => ['nullable', 'numeric'],
            'tabular_score' => ['nullable', 'numeric'],
            'symptoms_score' => ['nullable', 'numeric'],
        ]);


        $symptoms = [];
if ($request->filled('symptoms')) {
    $decoded = json_decode($request->input('symptoms'), true);
    $symptoms = is_array($decoded) ? $decoded : [];
}

$tests = [];
if ($request->filled('tests')) {
    $decoded = json_decode($request->input('tests'), true);
    $tests = is_array($decoded) ? $decoded : [];
}

$rawResult = null;
if ($request->filled('raw_result')) {
    $decoded = json_decode($request->input('raw_result'), true);
    $rawResult = json_last_error() === JSON_ERROR_NONE ? $decoded : $request->input('raw_result');
}

        $patient = $this->resolvePatient($user->id, $data);
        $analysis->update([
            'patient_id' => $patient->id,
            'age' => $data['age'] ?? $analysis->age,
            'troponin' => $this->parseNullableFloat($data['troponin'] ?? null),
            'ckmb' => $this->parseNullableFloat($data['ckmb'] ?? null),
            'result_score' => $this->parseNullableFloat($data['result_score'] ?? $data['probability'] ?? null),
            'result_text' => $data['result_text'] ?? null,
            'ecg_image_path' => $data['ecg_image_path'] ?? $analysis->ecg_image_path,
        ]);

       $file = $request->file('file');

if ($file) {
    $path = $file->store('ecg-images', 'public');

    $analysis->update([
        'ecg_image_path' => $path,
    ]);

    EcgImage::updateOrCreate(
        ['analysis_id' => $analysis->id],
        ['path' => $path]
    );
}

$result = Result::firstOrNew(['analysis_id' => $analysis->id]);
$result->probability = $this->parseNullableFloat($data['probability'] ?? $data['result_score'] ?? null);
$result->label = $this->normalizeLabel(
    $data['label'] ?? $data['result_text'] ?? ($rawResult['label'] ?? 'طبيعي')
);
$result->image_score = $this->parseNullableFloat($data['image_score'] ?? null);
$result->tabular_score = $this->parseNullableFloat($data['tabular_score'] ?? null);
$result->symptoms_score = $this->parseNullableFloat($data['symptoms_score'] ?? null);
$result->save();

if ($request->has('symptoms')) {
    $analysis->symptoms()->sync($this->resolveSymptoms($symptoms));
}
if ($request->has('tests')) {
    $analysis->tests()->sync($this->resolveTests($tests));
}

        return response()->json([
            'message' => 'Analysis updated',
            'data' => $this->transform($analysis->load(['patient', 'result', 'symptoms', 'tests', 'ecgImage'])),
        ]);
    }

    public function destroy(Request $request, Analysis $analysis)
    {
        $user = $this->requireUser($request);
        abort_unless($analysis->user_id === $user->id, 403);

        if ($analysis->ecgImage?->path) {
            Storage::disk('public')->delete($analysis->ecgImage->path);
        }

        $analysis->delete();

        return response()->json(['message' => 'Analysis deleted']);
    }

    protected function resolvePatient(int $userId, array $data): Patient
    {
        if (! empty($data['patient_id'])) {
            return Patient::where('id', $data['patient_id'])
                ->where('user_id', $userId)
                ->firstOrFail();
        }

        $name = trim((string) ($data['full_name'] ?? ''));
        abort_if($name === '', 422, 'full_name or patient_id is required');

        return Patient::firstOrCreate(
            [
                'user_id' => $userId,
                'full_name' => $name,
                'age' => $data['age'] ?? 0,
                'gender' => $this->normalizeGender($data['gender'] ?? null),
            ],
            [
                'age' => $data['age'] ?? 0,
                'gender' => $this->normalizeGender($data['gender'] ?? null),
            ]
        );
    }
     protected function parseNullableFloat($val)
    {
        if ($val === null || trim((string)$val) === 'null' || trim((string)$val) === '') {
            return null;
        }
        return (float) $val;
    }

    protected function resolveSymptoms(array $symptoms): array
{
    if (empty($symptoms)) return [];

    $payload = [];
    $idsByName = Symptom::pluck('id', 'name')->toArray();
    
    // Mapping for Arabic symptom names just in case they are sent in Arabic
    $arabicMapping = [
        'ألم صدر' => 'chest_pain',
        'ألم الصدر' => 'chest_pain',
        'ضيق تنفس' => 'shortness_of_breath',
        'ضيق التنفس' => 'shortness_of_breath',
        'غثيان' => 'nausea',
        'الغثيان' => 'nausea',
        'دوخة' => 'dizziness',
        'الدوخة' => 'dizziness',
        'تعرق' => 'sweating',
        'التعرق' => 'sweating',
        'إرهاق' => 'fatigue',
        'الإرهاق' => 'fatigue',
        'خفقان القلب' => 'palpitations',
        'خفقان' => 'palpitations',
        'shortness' => 'shortness_of_breath'
    ];

    $normalizeName = function($name) use ($arabicMapping) {
        $name = trim((string) $name);
        return $arabicMapping[$name] ?? $name;
    };

    if ($this->isAssoc($symptoms)) {
        foreach ($symptoms as $key => $value) {
            if ($value && $value !== 'false' && $value !== '0') {
                $name = $normalizeName($key);
                if (isset($idsByName[$name])) {
                    $payload[$idsByName[$name]] = ['value' => 1];
                } elseif (is_numeric($name)) {
                    $payload[(int)$name] = ['value' => 1];
                }
            }
        }
        return $payload;
    }

    foreach ($symptoms as $item) {
        if (is_numeric($item)) {
            $payload[(int)$item] = ['value' => 1];
            continue;
        }

        if (is_string($item)) {
            $name = $normalizeName($item);
            if (isset($idsByName[$name])) {
                $payload[$idsByName[$name]] = ['value' => 1];
            }
        } elseif (is_array($item)) {
            if (isset($item['id'])) {
                $payload[(int)$item['id']] = ['value' => 1];
            } elseif (isset($item['name'])) {
                $name = $normalizeName($item['name']);
                if (isset($idsByName[$name])) {
                    $payload[$idsByName[$name]] = ['value' => 1];
                }
            }
        }
    }

    return $payload;
}

    protected function resolveTests(array $tests): array
    {
        if ($tests === []) {
            return [];
        }

        if ($this->isAssoc($tests)) {
            $idsByName = Test::whereIn('name', array_keys($tests))->pluck('id', 'name')->all();
            $payload = [];

            foreach ($tests as $name => $value) {
                if (isset($idsByName[$name])) {
                    $payload[(int) $idsByName[$name]] = ['value' => is_numeric($value) ? (float) $value : null];
                } elseif (is_numeric($name)) {
                    $payload[(int) $name] = ['value' => is_numeric($value) ? (float) $value : null];
                }
            }

            return $payload;
        }

        $payload = [];
        foreach ($tests as $row) {
            if (is_array($row) && isset($row['id'])) {
                $payload[(int) $row['id']] = ['value' => is_numeric($row['value'] ?? null) ? (float) $row['value'] : null];
            }
        }

        return $payload;
    }

    protected function normalizeLabel(?string $label): string
    {
        $label = trim((string) $label);

        if ($label === '') {
            return 'طبيعي';
        }

        $normalized = Str::of(mb_strtolower($label))->replace(['_', '-', '/'], ' ')->squish()->toString();

        return match (true) {
            in_array($label, ['طبيعي', 'اضطراب بنبض القلب', 'مصاب بجلطة', 'مصاب بجلطة سابقة'], true) => $label,
            str_contains($normalized, 'arrhythm') || str_contains($normalized, 'اضطراب') || str_contains($normalized, 'نبض') => 'اضطراب بنبض القلب',
            str_contains($normalized, 'previous') || str_contains($normalized, 'old') || str_contains($normalized, 'سابق') => 'مصاب بجلطة سابقة',
            str_contains($normalized, 'positive') || str_contains($normalized, 'stroke') || str_contains($normalized, 'heart attack') || str_contains($normalized, 'جلطة') => 'مصاب بجلطة',
            str_contains($normalized, 'normal') || str_contains($normalized, 'negative') || str_contains($normalized, 'طبيعي') => 'طبيعي',
            default => $label,
        };
    }

    protected function normalizeGender(?string $gender): ?string
    {
        if ($gender === null || trim($gender) === '') {
            return null;
        }

        $gender = trim(mb_strtolower($gender));

        return match ($gender) {
            'male', 'ذكر', 'm', 'man', 'رجل' => 'male',
            'female', 'أنثى', 'انثى', 'f', 'woman', 'امرأة', 'امرأه' => 'female',
            default => 'other',
        };
    }

    protected function isAssoc(array $array): bool
    {
        return array_keys($array) !== range(0, count($array) - 1);
    }

    protected function transform(Analysis $analysis): array
    {
        return [
            'id' => $analysis->id,
            'user_id' => $analysis->user_id,
            'patient_id' => $analysis->patient_id,
            'patient' => [
                'id' => $analysis->patient?->id,
                'full_name' => $analysis->patient?->full_name,
                'gender' => $analysis->patient?->gender,
                'age' => $analysis->patient?->age,
            ],
            'age' => $analysis->age,
            'troponin' => $analysis->troponin,
            'ckmb' => $analysis->ckmb,
            'ecg_image_path' => $analysis->ecg_image_path,
            'ecg_image_url' => $analysis->ecg_image_path
    ? asset('storage/' . $analysis->ecg_image_path)
    : null,
            'result_score' => $analysis->result_score,
            'result_text' => $analysis->result_text,
            'symptoms' => $analysis->symptoms->map(fn ($symptom) => [
                'id' => $symptom->id,
                'name' => $symptom->name,
                'value' => (bool) $symptom->pivot?->value,
            ])->values(),
            'tests' => $analysis->tests->map(fn ($test) => [
                'id' => $test->id,
                'name' => $test->name,
                'value' => $test->pivot?->value,
            ])->values(),
            'result' => $analysis->result ? [
                'probability' => $analysis->result->probability,
                'label' => $analysis->result->label,
                'image_score' => $analysis->result->image_score,
                'tabular_score' => $analysis->result->tabular_score,
                'symptoms_score' => $analysis->result->symptoms_score,
            ] : null,
            'created_at' => optional($analysis->created_at)->toDateTimeString(),
        ];
    }
}
