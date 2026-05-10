<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Akun Manager (Super Admin)
        User::create([
            'name' => 'Sofyan Haris',
            'email' => 'manager@dom.com',
            'password' => Hash::make('password123'),
            'role' => 'manager',
        ]);

        // 2. Akun Kasir (Terbatas)
        User::create([
            'name' => 'Staff Kasir',
            'email' => 'kasir@dom.com',
            'password' => Hash::make('password123'),
            'role' => 'kasir',
        ]);
    }
}
