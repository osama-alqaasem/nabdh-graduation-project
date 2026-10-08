<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q'));
        $userId = $request->get('user_id');
        $patients = Patient::with('user')->withCount('analyses')
            ->when($q, fn ($query) => $query->where('full_name', 'like', "%{$q}%"))
            ->when($userId, fn ($query) => $query->where('user_id', $userId))
            ->latest()->paginate(15)->withQueryString();
        $users = User::orderBy('name')->get(['id','name']);
        return view('patients.index', compact('patients', 'users', 'q', 'userId'));
    }

    public function create()
    {
        $users = User::orderBy('name')->get(['id','name']);
        return view('patients.create', compact('users'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => ['required','exists:users,id'],
            'full_name' => ['required','string','max:255'],
            'gender' => ['required','in:male,female,other'],
            'age' => ['required','integer','min:0','max:120'],
        ]);
        Patient::create($data);
        return redirect()->route('dashboard.patients.index')->with('success', 'تمت إضافة المريض.');
    }

  public function show(Patient $patient)
{
   $patient->load([
    'user',
    'analyses.symptoms',
    'analyses.result',
    'analyses.ecgImage',
]);

    return view('dashboard.patients.show', compact('patient'));
}

    public function edit(Patient $patient)
    {
        $users = User::orderBy('name')->get(['id','name']);
        return view('patients.edit', compact('patient', 'users'));
    }

    public function update(Request $request, Patient $patient)
    {
        $data = $request->validate([
            'user_id' => ['required','exists:users,id'],
            'full_name' => ['required','string','max:255'],
            'gender' => ['required','in:male,female,other'],
            'age' => ['required','integer','min:0','max:120'],
        ]);
        $patient->update($data);
        return redirect()->route('dashboard.patients.index')->with('success', 'تم تحديث المريض.');
    }

    public function destroy(Patient $patient)
    {
        $patient->delete();
        return redirect()->route('dashboard.patients.index')->with('success', 'تم حذف المريض.');
    }
}
