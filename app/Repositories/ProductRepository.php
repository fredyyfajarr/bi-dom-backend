<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

class ProductRepository
{
    /**
     * @return Collection<int, Product>
     */
    public function getAllProducts(): Collection
    {
        return Product::with(['category', 'materials'])->get();
    }

    public function findProduct(int $id): Product
    {
        return Product::with(['category', 'materials'])->findOrFail($id);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createProduct(array $data): Product
    {
        return Product::create([
            'category_id' => $data['category_id'],
            'name' => $data['name'],
            'price' => $data['price'],
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateProduct(int $id, array $data): Product
    {
        $product = Product::findOrFail($id);

        $product->update([
            'category_id' => $data['category_id'],
            'name' => $data['name'],
            'price' => $data['price'],
        ]);

        return $product;
    }

    /**
     * @param  array<int, array<string, mixed>>  $syncData
     */
    public function syncMaterials(Product $product, array $syncData): void
    {
        $product->materials()->sync($syncData);
    }

    public function detachMaterials(Product $product): void
    {
        $product->materials()->detach();
    }

    public function deleteProduct(Product $product): void
    {
        $product->delete();
    }
}
