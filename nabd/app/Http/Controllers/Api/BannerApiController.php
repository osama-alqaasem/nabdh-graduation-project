<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;

class BannerApiController extends Controller
{
    public function index()
    {
        $banners = Banner::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $banners->transform(function ($banner) {
            return [
                'id' => $banner->id,
                'title' => $banner->title,
                'description' => $banner->description,
                'button_text' => $banner->button_text,
                'button_url' => $banner->button_url,

                // ✅ هنا أهم تعديل
                'image_url' => url('storage/' . $banner->image_path),
            ];
        });

        return response()->json($banners);
    }
}