<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Pastikan akun Manager Sofyan Haris selalu ada
        User::updateOrCreate(
            ['email' => 'sofyan.haris@domhub.com'], // Gunakan email unik
            [
                'name' => 'Sofyan Haris',
                'password' => Hash::make('password123'), // Silakan ganti passwordnya
                'role' => 'manager',
            ]
        );
    }
}
