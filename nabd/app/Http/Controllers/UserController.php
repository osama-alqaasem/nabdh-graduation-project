<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q'));
        $users = User::withCount(['patients', 'analyses'])
            ->when($q, fn ($query) => $query->where(fn ($sub) => $sub->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%")))
            ->latest()->paginate(15)->withQueryString();
        return view('users.index', compact('users', 'q'));
    }

    public function create() { return view('users.create'); }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'email' => ['required','email','max:255','unique:users,email'],
            'password' => ['required','string','min:8','confirmed'],
        ]);

        User::create(['name'=>$data['name'],'email'=>$data['email'],'password'=>Hash::make($data['password'])]);
        return redirect()->route('dashboard.users.index')->with('success', 'تمت إضافة المستخدم بنجاح.');
    }

    public function show(User $user)
    {
        $user->load(['patients.analyses.result', 'analyses.patient', 'analyses.result']);
        return view('users.show', compact('user'));
    }

    public function edit(User $user) { return view('users.edit', compact('user')); }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'email' => ['required','email','max:255','unique:users,email,'.$user->id],
            'password' => ['nullable','string','min:8','confirmed'],
        ]);
        $user->name = $data['name'];
        $user->email = $data['email'];
        if (!empty($data['password'])) $user->password = Hash::make($data['password']);
        $user->save();
        return redirect()->route('dashboard.users.index')->with('success', 'تم تحديث المستخدم.');
    }

    public function destroy(User $user)
    {
        if (auth()->id() === $user->id) return back()->with('error', 'لا يمكنك حذف حسابك الحالي.');
        $user->delete();
        return redirect()->route('dashboard.users.index')->with('success', 'تم حذف المستخدم.');
    }
}
