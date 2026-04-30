<?php

namespace Database\Seeders;

use App\Models\Inventory;
use Illuminate\Database\Seeder;

class InventorySeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'item_name' => 'Biji Kopi House Blend',
                'unit' => 'KG',
                'current_stock' => 10.00,
                'min_stock' => 2.00,
                'usage_per_trx' => 0.05 // Asumsi 50 gram per transaksi
            ],
            [
                'item_name' => 'Susu Fresh Milk',
                'unit' => 'Liter',
                'current_stock' => 20.00,
                'min_stock' => 5.00,
                'usage_per_trx' => 0.15 // Asumsi 150 ml per transaksi
            ],
            [
                'item_name' => 'Gula Aren Cair',
                'unit' => 'Liter',
                'current_stock' => 5.00,
                'min_stock' => 1.00,
                'usage_per_trx' => 0.03 // Asumsi 30 ml per transaksi
            ],
            [
                'item_name' => 'Bubuk Matcha Premium',
                'unit' => 'KG',
                'current_stock' => 3.00,
                'min_stock' => 0.50,
                'usage_per_trx' => 0.02 // Asumsi 20 gram per transaksi
            ],
        ];

        foreach ($items as $item) {
            Inventory::updateOrCreate(['item_name' => $item['item_name']], $item);
        }
    }
}
