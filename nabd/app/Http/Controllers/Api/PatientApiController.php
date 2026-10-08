<?php

namespace App\Http\Controllers\Api;

use App\Models\Analysis;
use App\Models\Patient;
use Illuminate\Http\Request;

class PatientApiController extends ApiController
{
    public function index(Request $request)
    {
        $user = $this->requireUser($request);

        $patients = Patient::with(['analyses.result'])
            ->where('user_id', $user->id)
            ->latest()
            ->get()
            ->map(function (Patient $patient) {
                $latestAnalysis = $patient->analyses->sortByDesc('created_at')->first();

                return [
                    'id' => $patient->id,
                    'user_id' => $patient->user_id,
                    'full_name' => $patient->full_name,
                    'gender' => $patient->gender,
                    'age' => $patient->age,
                    'visits_count' => $patient->analyses->count(),
                    'latest_result_label' => $latestAnalysis?->result?->label,
                    'latest_result_score' => $latestAnalysis?->result?->probability,
                    'created_at' => optional($patient->created_at)->toDateTimeString(),
                    'updated_at' => optional($patient->updated_at)->toDateTimeString(),
                ];
            });

        return response()->json(['data' => $patients]);
    }

    public function store(Request $request)
    {
        $user = $this->requireUser($request);

        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'gender' => ['nullable', 'string', 'max:20'],
            'age' => ['nullable', 'integer', 'min:0', 'max:120'],
        ]);

        $patient = Patient::create([
            'user_id' => $user->id,
            'full_name' => $data['full_name'],
            'gender' => $this->normalizeGender($data['gender'] ?? null),
            'age' => $data['age'] ?? 0,
        ]);

        return response()->json([
            'message' => 'Patient created',
            'data' => $patient,
        ], 201);
    }

    public function show(Request $request, Patient $patient)
    {
        $user = $this->requireUser($request);
        abort_unless($patient->user_id === $user->id, 403);

        $patient->load(['analyses.result', 'analyses.symptoms', 'analyses.tests', 'analyses.ecgImage']);

        $patientData = [
            'id' => $patient->id,
            'user_id' => $patient->user_id,
            'full_name' => $patient->full_name,
            'gender' => $patient->gender,
            'age' => $patient->age,
            'analyses_count' => $patient->analyses->count(),
        ];

        $analyses = $patient->analyses->map(function ($analysis) {
            return [
                'id' => $analysis->id,
                'result_text' => $analysis->result?->label ?? $analysis->result_text,
                'created_at' => optional($analysis->created_at)->toDateTimeString(),
                'result' => $analysis->result ? [
                    'probability' => $analysis->result->probability,
                    'label' => $analysis->result->label,
                    'image_score' => $analysis->result->image_score,
                    'tabular_score' => $analysis->result->tabular_score,
                    'symptoms_score' => $analysis->result->symptoms_score,
                ] : null,
                'symptoms' => $analysis->symptoms->map(fn ($symptom) => [
                    'id' => $symptom->id,
                    'name' => $symptom->name,
                    'value' => (bool) $symptom->pivot?->value,
                ]),
                'tests' => $analysis->tests->map(fn ($test) => [
                    'id' => $test->id,
                    'name' => $test->name,
                    'value' => $test->pivot?->value,
                ]),
            ];
        });

        return response()->json([
            'data' => [
                'patient' => $patientData,
                'analyses' => $analyses,
            ],
            'patient' => $patientData,
            'analyses' => $analyses,
        ]);
    }

    public function update(Request $request, Patient $patient)
    {
        $user = $this->requireUser($request);
        abort_unless($patient->user_id === $user->id, 403);

        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'gender' => ['nullable', 'string', 'max:20'],
            'age' => ['nullable', 'integer', 'min:0', 'max:120'],
        ]);

        $patient->update([
            'full_name' => $data['full_name'],
            'gender' => $this->normalizeGender($data['gender'] ?? $patient->gender),
            'age' => $data['age'] ?? $patient->age,
        ]);

        return response()->json([
            'message' => 'Patient updated',
            'data' => $patient,
        ]);
    }

    public function destroy(Request $request, Patient $patient)
    {
        $user = $this->requireUser($request);
        abort_unless($patient->user_id === $user->id, 403);

        $patient->delete();

        return response()->json(['message' => 'Patient deleted']);
    }

    public function history(Request $request)
    {
        $user = $this->requireUser($request);

        $data = Analysis::with(['patient', 'result', 'symptoms', 'tests', 'ecgImage'])
            ->where('user_id', $user->id)
            ->latest()
            ->get()
            ->map(function (Analysis $analysis) {
                return [
                    'id' => $analysis->id,
                    'user_id' => $analysis->user_id,
                    'patient_id' => $analysis->patient_id,
                    'draft' => [
                        'name' => $analysis->patient?->full_name,
                        'age' => $analysis->age,
                        'gender' => $analysis->patient?->gender,
                        'patientId' => $analysis->patient_id,
                        'ecgImagePath' => $analysis->ecg_image_path,
                        'symptoms' => $analysis->symptoms->pluck('name')->values(),
                        'troponin' => $analysis->troponin,
                        'ckmb' => $analysis->ckmb,
                    ],
                    'result' => $analysis->result ? [
                        'isPositive' => $analysis->result->label !== 'طبيعي',
                        'label' => $analysis->result->label,
                        'message' => $analysis->result->label,
                        'confidence' => $analysis->result->probability,
                        'imageScore' => $analysis->result->image_score,
                        'bloodScore' => $analysis->result->tabular_score,
                        'symptomsScore' => $analysis->result->symptoms_score,
                        'raw' => [
                            'result' => $analysis->result->label,
                            'score' => $analysis->result->probability,
                        ],
                    ] : null,
                    'userId' => (string) $analysis->user_id,
                    'createdAt' => optional($analysis->created_at)->toIso8601String(),
                ];
            });

        return response()->json(['data' => $data]);
    }

    protected function normalizeGender(?string $gender): string
    {
        $gender = trim(mb_strtolower((string) $gender));

        return match ($gender) {
            'male', 'ذكر', 'm', 'man', 'رجل' => 'male',
            'female', 'أنثى', 'انثى', 'f', 'woman', 'امرأة', 'امرأه' => 'female',
            default => 'other',
        };
    }
}
