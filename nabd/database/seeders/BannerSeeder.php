<?php

namespace Database\Seeders;

use App\Models\Banner;
use Illuminate\Database\Seeder;

class BannerSeeder extends Seeder
{
    public function run(): void
    {
        Banner::firstOrCreate(['title' => 'إعلان افتراضي'], [
            'description' => 'يمكن تغيير هذه الصورة والنص من لوحة التحكم لتظهر داخل التطبيق.',
            'image_path' => 'banners/sample-banner.jpg',
            'button_text' => 'اعرف المزيد',
            'button_url' => 'https://example.com',
            'sort_order' => 1,
            'is_active' => true,
            'created_by' => null,
        ]);
    }
}
