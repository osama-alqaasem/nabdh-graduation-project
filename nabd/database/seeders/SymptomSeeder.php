<?php

namespace Database\Seeders;

use App\Models\Symptom;
use Illuminate\Database\Seeder;

class SymptomSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['chest_pain','dizziness','nausea','sweating','shortness_of_breath','palpitations'] as $name) {
            Symptom::firstOrCreate(['name' => $name]);
        }
    }
}
