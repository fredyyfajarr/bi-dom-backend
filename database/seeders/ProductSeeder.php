    <?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = collect([
            'Coffee',
            'Non-Coffee',
            'Tea',
            'Mocktail',
            'Frappe',
            'Main Course',
            'Finger Foods',
        ])->mapWithKeys(fn (string $name) => [
            $name => tap(
                Category::withTrashed()->updateOrCreate(['name' => $name], ['name' => $name]),
                fn (Category $category) => $category->trashed() ? $category->restore() : null,
            ),
        ]);

        $products = [
            ['Coffee', 'Americano', 20000, 6500, ['Biji Kopi House Blend' => 0.018]],
            ['Coffee', 'Espresso On The Rock', 25000, 7500, ['Biji Kopi House Blend' => 0.018]],
            ['Coffee', 'Double Shaken Espresso', 29000, 10000, ['Biji Kopi House Blend' => 0.022, 'Susu Fresh Milk' => 0.080, 'Gula Aren Cair' => 0.025]],
            ['Coffee', "DOM's Original", 25000, 9500, ['Biji Kopi House Blend' => 0.018, 'Susu Fresh Milk' => 0.120, 'Gula Aren Cair' => 0.030]],
            ['Coffee', 'Kopi Latte', 25000, 9000, ['Biji Kopi House Blend' => 0.018, 'Susu Fresh Milk' => 0.160]],
            ['Coffee', "Sub's Choice", 35000, 14000, ['Biji Kopi House Blend' => 0.022, 'Susu Fresh Milk' => 0.150, 'Syrup Salted Caramel' => 0.020]],
            ['Coffee', 'Hazelnut Latte', 32000, 12500, ['Biji Kopi House Blend' => 0.018, 'Susu Fresh Milk' => 0.150, 'Syrup Hazelnut' => 0.020]],
            ['Coffee', 'Caramel Latte', 32000, 12500, ['Biji Kopi House Blend' => 0.018, 'Susu Fresh Milk' => 0.150, 'Syrup Caramel' => 0.020]],
            ['Coffee', 'Salted Caramel Macchiato', 37000, 14500, ['Biji Kopi House Blend' => 0.018, 'Susu Fresh Milk' => 0.150, 'Syrup Salted Caramel' => 0.025]],
            ['Coffee', 'Scottish Latte', 32000, 12500, ['Biji Kopi House Blend' => 0.018, 'Susu Fresh Milk' => 0.150, 'Syrup Caramel' => 0.015]],

            ['Non-Coffee', 'Chocolate', 27000, 10500, ['Chocolate Powder' => 0.040, 'Susu Fresh Milk' => 0.180]],
            ['Non-Coffee', 'Choco Loco', 32000, 13000, ['Chocolate Powder' => 0.050, 'Susu Fresh Milk' => 0.180, 'Syrup Caramel' => 0.015]],
            ['Non-Coffee', 'Matcha Green Tea', 38000, 15500, ['Matcha Powder' => 0.025, 'Susu Fresh Milk' => 0.180]],
            ['Non-Coffee', 'Red Velvet', 28000, 11500, ['Red Velvet Powder' => 0.035, 'Susu Fresh Milk' => 0.180]],
            ['Non-Coffee', 'Taro Latte With Egg Pudding', 32000, 13500, ['Taro Powder' => 0.030, 'Susu Fresh Milk' => 0.160, 'Egg Pudding' => 1.000]],
            ['Non-Coffee', 'Violet Kalamansi', 28000, 10000, ['Kalamansi Concentrate' => 0.030, 'Soda Water' => 0.160]],

            ['Tea', 'Lemon Tea', 25000, 7500, ['Black Tea Base' => 0.180, 'Lemon Syrup' => 0.030]],
            ['Tea', 'Lychee Tea', 25000, 8500, ['Black Tea Base' => 0.180, 'Lychee Syrup' => 0.030]],
            ['Tea', 'Strawberry Tea', 25000, 8500, ['Black Tea Base' => 0.180, 'Strawberry Syrup' => 0.030]],

            ['Mocktail', 'Tropical Punch', 31000, 12000, ['Tropical Fruit Mix' => 0.050, 'Soda Water' => 0.180]],
            ['Mocktail', 'Saltovino', 33000, 12500, ['Kalamansi Concentrate' => 0.030, 'Soda Water' => 0.200]],
            ['Mocktail', 'Bloody Mary', 31000, 12000, ['Tropical Fruit Mix' => 0.040, 'Soda Water' => 0.180]],
            ['Mocktail', 'Sunset Sangria', 31000, 12000, ['Berry Compote' => 0.050, 'Soda Water' => 0.180]],

            ['Frappe', 'Bang Nana Yoghurt', 40000, 17000, ['Yoghurt Base' => 0.160, 'Banana Puree' => 0.080, 'Frappe Base' => 0.050]],
            ['Frappe', 'Muppet', 38000, 16000, ['Frappe Base' => 0.060, 'Susu Fresh Milk' => 0.140, 'Chocolate Powder' => 0.030]],
            ['Frappe', 'Candy Cane', 39000, 16500, ['Frappe Base' => 0.060, 'Susu Fresh Milk' => 0.140, 'Syrup Caramel' => 0.020]],
            ['Frappe', 'Mr. P! Nuts', 39000, 17000, ['Frappe Base' => 0.060, 'Susu Fresh Milk' => 0.140, 'Peanut Butter' => 0.040]],
            ['Frappe', 'Lotus', 38000, 16500, ['Frappe Base' => 0.060, 'Susu Fresh Milk' => 0.140, 'Lotus Biscuit' => 0.040]],
            ['Frappe', 'Berry Berry Good!', 38000, 16000, ['Frappe Base' => 0.060, 'Yoghurt Base' => 0.120, 'Berry Compote' => 0.050]],

            ['Main Course', "Chick N' Matah Rice", 35000, 17000, ['Beras' => 0.180, 'Ayam Fillet' => 0.120, 'Sambal Matah' => 0.050]],
            ['Main Course', 'Oriental Fried Rice', 32000, 14500, ['Beras' => 0.200, 'Ayam Fillet' => 0.080, 'Bumbu Nasi Goreng' => 0.050]],
            ['Main Course', 'Kampoeng Fried Rice', 32000, 14500, ['Beras' => 0.200, 'Ayam Fillet' => 0.080, 'Bumbu Nasi Goreng' => 0.060]],
            ['Main Course', 'Salted Egg Pop Chicken', 45000, 22000, ['Ayam Fillet' => 0.180, 'Salted Egg Sauce' => 0.080, 'Beras' => 0.150]],
            ['Main Course', 'Mayo Karaage Rice', 40000, 19500, ['Ayam Fillet' => 0.170, 'Beras' => 0.180, 'Cream Sauce' => 0.030]],
            ['Main Course', 'Ham Spaghetti Brulee', 38000, 18000, ['Spaghetti Pasta' => 0.110, 'Ham Slice' => 0.060, 'Cream Sauce' => 0.100, 'Cheese Mix' => 0.030]],
            ['Main Course', 'Beef Aglio Olio', 32000, 17000, ['Spaghetti Pasta' => 0.110, 'Beef Slice' => 0.070]],
            ['Main Course', 'Spaghetti Bolognese', 32000, 16000, ['Spaghetti Pasta' => 0.110, 'Bolognese Sauce' => 0.120]],
            ['Main Course', 'Spaghetti Carbonara', 35000, 17500, ['Spaghetti Pasta' => 0.110, 'Cream Sauce' => 0.120, 'Cheese Mix' => 0.020]],

            ['Finger Foods', 'French Fries & Sausage', 25000, 11500, ['Kentang Frozen' => 0.180, 'Sosis' => 1.000]],
            ['Finger Foods', 'Al Fresco Nachos', 30000, 13000, ['Nachos Chips' => 0.100, 'Cheese Mix' => 0.050]],
            ['Finger Foods', 'Cheese Croquette Balls', 25000, 11500, ['Cheese Mix' => 0.080, 'Kentang Frozen' => 0.120]],
            ['Finger Foods', 'DOMS Wings', 30000, 15000, ['Chicken Wings' => 0.250]],
            ['Finger Foods', 'Pisang Gorengnya DOM', 26000, 11000, ['Pisang' => 0.200, 'Cheese Mix' => 0.020]],
            ['Finger Foods', 'Roti Bakar', 20000, 8500, ['Roti Tawar' => 2.000, 'Susu Fresh Milk' => 0.030, 'Cheese Mix' => 0.020]],
            ['Finger Foods', 'Churro Bites', 25000, 10500, ['Churro Dough' => 0.150, 'Chocolate Powder' => 0.020]],
        ];

        $inventories = Inventory::all()->keyBy('item_name');

        foreach ($products as [$categoryName, $name, $price, $cogs, $recipe]) {
            $product = Product::withTrashed()->updateOrCreate(
                ['name' => $name],
                [
                    'category_id' => $categories[$categoryName]->id,
                    'price' => $price,
                    'cogs' => $cogs,
                ],
            );
            if ($product->trashed()) {
                $product->restore();
            }

            $syncData = [];
            foreach ($recipe as $itemName => $usageQty) {
                $inventory = $inventories[$itemName] ?? null;
                if ($inventory) {
                    $syncData[$inventory->id] = ['usage_qty' => $usageQty];
                }
            }

            $product->materials()->sync($syncData);
        }


    }
}
