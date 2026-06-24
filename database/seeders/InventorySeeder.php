<?php

namespace Database\Seeders;

use App\Models\Inventory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InventorySeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['item_name' => 'Biji Kopi House Blend', 'unit' => 'KG', 'unit_cost' => 150000.00, 'current_stock' => 6.00, 'min_stock' => 2.00, 'usage_per_trx' => 0.045],
            ['item_name' => 'Susu Fresh Milk', 'unit' => 'Liter', 'unit_cost' => 18000.00, 'current_stock' => 10.00, 'min_stock' => 4.00, 'usage_per_trx' => 0.120],
            ['item_name' => 'Gula Aren Cair', 'unit' => 'Liter', 'unit_cost' => 25000.00, 'current_stock' => 3.00, 'min_stock' => 1.00, 'usage_per_trx' => 0.025],
            ['item_name' => 'Syrup Hazelnut', 'unit' => 'Liter', 'unit_cost' => 80000.00, 'current_stock' => 2.00, 'min_stock' => 0.60, 'usage_per_trx' => 0.012],
            ['item_name' => 'Syrup Caramel', 'unit' => 'Liter', 'unit_cost' => 80000.00, 'current_stock' => 2.00, 'min_stock' => 0.60, 'usage_per_trx' => 0.014],
            ['item_name' => 'Syrup Salted Caramel', 'unit' => 'Liter', 'unit_cost' => 80000.00, 'current_stock' => 1.60, 'min_stock' => 0.50, 'usage_per_trx' => 0.010],
            ['item_name' => 'Chocolate Powder', 'unit' => 'KG', 'unit_cost' => 75000.00, 'current_stock' => 3.00, 'min_stock' => 0.80, 'usage_per_trx' => 0.030],
            ['item_name' => 'Matcha Powder', 'unit' => 'KG', 'unit_cost' => 120000.00, 'current_stock' => 1.60, 'min_stock' => 0.50, 'usage_per_trx' => 0.018],
            ['item_name' => 'Red Velvet Powder', 'unit' => 'KG', 'unit_cost' => 90000.00, 'current_stock' => 1.80, 'min_stock' => 0.50, 'usage_per_trx' => 0.018],
            ['item_name' => 'Taro Powder', 'unit' => 'KG', 'unit_cost' => 90000.00, 'current_stock' => 1.40, 'min_stock' => 0.40, 'usage_per_trx' => 0.014],
            ['item_name' => 'Egg Pudding', 'unit' => 'Porsi', 'unit_cost' => 5000.00, 'current_stock' => 20.00, 'min_stock' => 6.00, 'usage_per_trx' => 0.120],
            ['item_name' => 'Black Tea Base', 'unit' => 'Liter', 'unit_cost' => 10000.00, 'current_stock' => 8.00, 'min_stock' => 2.50, 'usage_per_trx' => 0.100],
            ['item_name' => 'Lemon Syrup', 'unit' => 'Liter', 'unit_cost' => 50000.00, 'current_stock' => 2.00, 'min_stock' => 0.60, 'usage_per_trx' => 0.012],
            ['item_name' => 'Lychee Syrup', 'unit' => 'Liter', 'unit_cost' => 50000.00, 'current_stock' => 2.00, 'min_stock' => 0.60, 'usage_per_trx' => 0.012],
            ['item_name' => 'Strawberry Syrup', 'unit' => 'Liter', 'unit_cost' => 50000.00, 'current_stock' => 2.00, 'min_stock' => 0.60, 'usage_per_trx' => 0.012],
            ['item_name' => 'Soda Water', 'unit' => 'Liter', 'unit_cost' => 12000.00, 'current_stock' => 12.00, 'min_stock' => 4.00, 'usage_per_trx' => 0.100],
            ['item_name' => 'Tropical Fruit Mix', 'unit' => 'Liter', 'unit_cost' => 40000.00, 'current_stock' => 2.50, 'min_stock' => 0.80, 'usage_per_trx' => 0.020],
            ['item_name' => 'Kalamansi Concentrate', 'unit' => 'Liter', 'unit_cost' => 60000.00, 'current_stock' => 1.80, 'min_stock' => 0.50, 'usage_per_trx' => 0.012],
            ['item_name' => 'Yoghurt Base', 'unit' => 'Liter', 'unit_cost' => 35000.00, 'current_stock' => 5.00, 'min_stock' => 1.50, 'usage_per_trx' => 0.050],
            ['item_name' => 'Banana Puree', 'unit' => 'KG', 'unit_cost' => 45000.00, 'current_stock' => 3.00, 'min_stock' => 0.80, 'usage_per_trx' => 0.030],
            ['item_name' => 'Frappe Base', 'unit' => 'KG', 'unit_cost' => 60000.00, 'current_stock' => 3.00, 'min_stock' => 0.80, 'usage_per_trx' => 0.030],
            ['item_name' => 'Peanut Butter', 'unit' => 'KG', 'unit_cost' => 55000.00, 'current_stock' => 1.50, 'min_stock' => 0.40, 'usage_per_trx' => 0.012],
            ['item_name' => 'Lotus Biscuit', 'unit' => 'KG', 'unit_cost' => 90000.00, 'current_stock' => 1.50, 'min_stock' => 0.40, 'usage_per_trx' => 0.012],
            ['item_name' => 'Berry Compote', 'unit' => 'KG', 'unit_cost' => 80000.00, 'current_stock' => 2.00, 'min_stock' => 0.50, 'usage_per_trx' => 0.014],
            ['item_name' => 'Beras', 'unit' => 'KG', 'unit_cost' => 15000.00, 'current_stock' => 18.00, 'min_stock' => 6.00, 'usage_per_trx' => 0.180],
            ['item_name' => 'Ayam Fillet', 'unit' => 'KG', 'unit_cost' => 65000.00, 'current_stock' => 8.00, 'min_stock' => 3.00, 'usage_per_trx' => 0.100],
            ['item_name' => 'Sambal Matah', 'unit' => 'KG', 'unit_cost' => 40000.00, 'current_stock' => 3.00, 'min_stock' => 1.00, 'usage_per_trx' => 0.025],
            ['item_name' => 'Bumbu Nasi Goreng', 'unit' => 'KG', 'unit_cost' => 35000.00, 'current_stock' => 3.00, 'min_stock' => 1.00, 'usage_per_trx' => 0.025],
            ['item_name' => 'Salted Egg Sauce', 'unit' => 'KG', 'unit_cost' => 70000.00, 'current_stock' => 2.50, 'min_stock' => 0.80, 'usage_per_trx' => 0.020],
            ['item_name' => 'Spaghetti Pasta', 'unit' => 'KG', 'unit_cost' => 30000.00, 'current_stock' => 7.00, 'min_stock' => 2.00, 'usage_per_trx' => 0.070],
            ['item_name' => 'Beef Slice', 'unit' => 'KG', 'unit_cost' => 130000.00, 'current_stock' => 4.00, 'min_stock' => 1.20, 'usage_per_trx' => 0.035],
            ['item_name' => 'Ham Slice', 'unit' => 'KG', 'unit_cost' => 110000.00, 'current_stock' => 3.00, 'min_stock' => 0.80, 'usage_per_trx' => 0.025],
            ['item_name' => 'Cream Sauce', 'unit' => 'Liter', 'unit_cost' => 60000.00, 'current_stock' => 4.00, 'min_stock' => 1.20, 'usage_per_trx' => 0.040],
            ['item_name' => 'Bolognese Sauce', 'unit' => 'KG', 'unit_cost' => 50000.00, 'current_stock' => 4.00, 'min_stock' => 1.20, 'usage_per_trx' => 0.040],
            ['item_name' => 'Kentang Frozen', 'unit' => 'KG', 'unit_cost' => 35000.00, 'current_stock' => 7.00, 'min_stock' => 2.50, 'usage_per_trx' => 0.080],
            ['item_name' => 'Sosis', 'unit' => 'PCS', 'unit_cost' => 3000.00, 'current_stock' => 35.00, 'min_stock' => 12.00, 'usage_per_trx' => 0.350],
            ['item_name' => 'Nachos Chips', 'unit' => 'KG', 'unit_cost' => 45000.00, 'current_stock' => 3.00, 'min_stock' => 0.80, 'usage_per_trx' => 0.030],
            ['item_name' => 'Cheese Mix', 'unit' => 'KG', 'unit_cost' => 95000.00, 'current_stock' => 4.00, 'min_stock' => 1.00, 'usage_per_trx' => 0.040],
            ['item_name' => 'Chicken Wings', 'unit' => 'KG', 'unit_cost' => 75000.00, 'current_stock' => 5.00, 'min_stock' => 1.80, 'usage_per_trx' => 0.050],
            ['item_name' => 'Pisang', 'unit' => 'KG', 'unit_cost' => 12000.00, 'current_stock' => 4.00, 'min_stock' => 1.20, 'usage_per_trx' => 0.035],
            ['item_name' => 'Roti Tawar', 'unit' => 'PCS', 'unit_cost' => 1500.00, 'current_stock' => 40.00, 'min_stock' => 12.00, 'usage_per_trx' => 0.300],
            ['item_name' => 'Churro Dough', 'unit' => 'KG', 'unit_cost' => 35000.00, 'current_stock' => 3.00, 'min_stock' => 1.00, 'usage_per_trx' => 0.030],
        ];

        foreach ($items as $item) {
            Inventory::updateOrCreate(['item_name' => $item['item_name']], $item);
        }

        $legacyInventoryIds = Inventory::whereIn('item_name', [
            'Bubuk Matcha Premium',
        ])->pluck('id');

        DB::table('product_inventory')->whereIn('inventory_id', $legacyInventoryIds)->delete();
        Inventory::whereIn('id', $legacyInventoryIds)->delete();
    }
}
