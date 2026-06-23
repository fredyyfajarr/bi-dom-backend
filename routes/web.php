<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

Route::get('/', function () {
    return view('welcome');
});

// Route sementara untuk melakukan migrasi, seeding, dan clear cache di server production online
Route::get('/run-online-fix', function () {
    // Naikkan limit memori dan waktu eksekusi agar tidak timeout di shared hosting
    ini_set('memory_limit', '512M');
    set_time_limit(300); // 5 menit

    try {
        // Jalankan migrate:fresh --seed secara paksa (--force dibutuhkan di environment production)
        Artisan::call('migrate:fresh', [
            '--seed' => true,
            '--force' => true
        ]);

        // Bersihkan seluruh cache konfigurasi Laravel
        Artisan::call('optimize:clear');

        return response()->json([
            'success' => true,
            'message' => 'Online database migrated, seeded, and caches cleared successfully!'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
});

