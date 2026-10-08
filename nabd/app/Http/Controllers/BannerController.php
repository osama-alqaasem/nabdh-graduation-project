<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q'));
        $banners = Banner::when($q, fn ($query) => $query->where('title', 'like', "%{$q}%"))->latest()->paginate(12)->withQueryString();
        return view('banners.index', compact('banners', 'q'));
    }

    public function create() { return view('banners.create'); }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required','string','max:255'],
            'description' => ['nullable','string'],
            'button_text' => ['nullable','string','max:255'],
            'button_url' => ['nullable','url','max:255'],
            'sort_order' => ['nullable','integer'],
            'is_active' => ['nullable','boolean'],
            'image' => ['required','image','max:4096'],
        ]);
        $path = $request->file('image')->store('banners', 'public');
        Banner::create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'image_path' => $path,
            'button_text' => $data['button_text'] ?? null,
            'button_url' => $data['button_url'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
            'created_by' => auth()->id(),
        ]);
        return redirect()->route('dashboard.banners.index')->with('success', 'تمت إضافة الإعلان.');
    }

    public function edit(Banner $banner) { return view('banners.edit', compact('banner')); }

    public function update(Request $request, Banner $banner)
    {
        $data = $request->validate([
            'title' => ['required','string','max:255'],
            'description' => ['nullable','string'],
            'button_text' => ['nullable','string','max:255'],
            'button_url' => ['nullable','url','max:255'],
            'sort_order' => ['nullable','integer'],
            'is_active' => ['nullable','boolean'],
            'image' => ['nullable','image','max:4096'],
        ]);

        if ($request->hasFile('image')) {
            Storage::disk('public')->delete($banner->image_path);
            $banner->image_path = $request->file('image')->store('banners', 'public');
        }

        $banner->fill([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'button_text' => $data['button_text'] ?? null,
            'button_url' => $data['button_url'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);
        $banner->save();
        return redirect()->route('dashboard.banners.index')->with('success', 'تم تحديث الإعلان.');
    }

    public function destroy(Banner $banner)
    {
        Storage::disk('public')->delete($banner->image_path);
        $banner->delete();
        return redirect()->route('dashboard.banners.index')->with('success', 'تم حذف الإعلان.');
    }
}
