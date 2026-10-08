<?php

namespace Database\Seeders;

use App\Models\Test;
use Illuminate\Database\Seeder;

class TestSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['troponin','ckmb'] as $name) {
            Test::firstOrCreate(['name' => $name]);
        }
    }
}
