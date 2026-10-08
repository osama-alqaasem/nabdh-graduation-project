<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;


class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
            User::firstOrCreate(['email' => 'admin1@gmail.com'], [
            'name' => 'Admin',
            'password' => Hash::make('123456789'),
        ]);
    }
}
