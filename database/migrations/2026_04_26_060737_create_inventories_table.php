<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->string('item_name');
            $table->decimal('current_stock', 8, 2);
            $table->decimal('min_stock', 8, 2);
            $table->decimal('usage_per_trx', 8, 4); // Estimasi pemakaian per transaksi
            $table->string('unit'); // kg, liter, dll
            $table->timestamps();
        });

        // Insert data dummy untuk DOM Social Hub
        DB::table('inventories')->insert([
            [
                'item_name' => 'Biji Kopi House Blend',
                'current_stock' => 5.00, // 5 kg tersisa
                'min_stock' => 2.00,     // Batas peringatan 2 kg
                'usage_per_trx' => 0.018, // 18 gram per cup/transaksi
                'unit' => 'kg'
            ],
            [
                'item_name' => 'Susu Fresh Milk',
                'current_stock' => 10.00, // 10 liter
                'min_stock' => 5.00,
                'usage_per_trx' => 0.150, // 150 ml per cup/transaksi
                'unit' => 'liter'
            ]
        ]);
    }

    public function down(): void {
        Schema::dropIfExists('inventories');
    }
};
