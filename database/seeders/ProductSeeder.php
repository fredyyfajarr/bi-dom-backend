<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat Kategori
        $catCoffee = Category::create(['name' => 'Coffee']);
        $catNonCoffee = Category::create(['name' => 'Non-Coffee']);
        $catFood = Category::create(['name' => 'Main Course']);
        $catSnack = Category::create(['name' => 'Snacks']);

        // 2. Buat Produk
        $products = [
            // Coffee
            ['category_id' => $catCoffee->id, 'name' => 'Kopi Susu DOM', 'price' => 25000],
            ['category_id' => $catCoffee->id, 'name' => 'Americano', 'price' => 20000],
            ['category_id' => $catCoffee->id, 'name' => 'Caramel Macchiato', 'price' => 30000],

            // Non-Coffee
            ['category_id' => $catNonCoffee->id, 'name' => 'Zafeer Milktea', 'price' => 28000],
            ['category_id' => $catNonCoffee->id, 'name' => 'Matcha Latte', 'price' => 28000],
            ['category_id' => $catNonCoffee->id, 'name' => 'Lychee Tea', 'price' => 22000],

            // Food
            ['category_id' => $catFood->id, 'name' => 'Ayam Chili Padi', 'price' => 35000],
            ['category_id' => $catFood->id, 'name' => 'Nasi Goreng DOM', 'price' => 30000],
            ['category_id' => $catFood->id, 'name' => 'Spaghetti Aglio Olio', 'price' => 32000],

            // Snack
            ['category_id' => $catSnack->id, 'name' => 'Mix Platter', 'price' => 35000],
            ['category_id' => $catSnack->id, 'name' => 'French Fries', 'price' => 20000],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
