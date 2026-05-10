<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,        // Login Manager dan Kasir
            InventorySeeder::class,   // Daftar Stok Bahan Baku
            ProductSeeder::class,     // Daftar Menu (Kopi, Makanan, dll)
            RealisticTransactionSeeder::class, // Generate data transaksi yang realistis selama 16 bulan terakhir
        ]);
    }
}
