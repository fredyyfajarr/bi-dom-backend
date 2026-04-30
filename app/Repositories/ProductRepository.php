<?php

namespace App\Repositories;

use App\Models\Product;

class ProductRepository
{
    public function getAllProducts()
    {
        // Ambil semua produk beserta relasi kategori dan resep
        return Product::with(['category', 'materials'])->get();
    }

    public function createProduct(array $data)
    {
        return Product::create([
            'category_id' => $data['category_id'],
            'name' => $data['name'],
            'price' => $data['price'],
        ]);
    }

    public function updateProduct($id, array $data)
    {
        $product = Product::findOrFail($id);

        $product->update([
            'category_id' => $data['category_id'],
            'name' => $data['name'],
            'price' => $data['price'],
        ]);

        return $product;
    }

    public function syncMaterials(Product $product, array $syncData)
    {
        // Menyimpan relasi ke tabel pivot (product_inventory)
        $product->materials()->sync($syncData);
    }
}
