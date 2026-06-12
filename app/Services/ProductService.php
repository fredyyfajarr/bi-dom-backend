<?php

namespace App\Services;

use App\Models\Product;
use App\Repositories\ProductRepository;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ProductService
{
    public function __construct(private readonly ProductRepository $repo) {}

    /**
     * @return Collection<int, Product>
     */
    public function getAllProducts(): Collection
    {
        return $this->repo->getAllProducts();
    }

    public function findProduct(int $id): Product
    {
        return $this->repo->findProduct($id);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function saveProductAndRecipe(array $data): Product
    {
        return DB::transaction(function () use ($data) {
            $product = $this->repo->createProduct($data);
            $syncData = $this->formatSyncData($data['materials'] ?? []);

            $this->repo->syncMaterials($product, $syncData);

            return $product;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateProductAndRecipe(int $id, array $data): Product
    {
        return DB::transaction(function () use ($id, $data) {
            $product = $this->repo->updateProduct($id, $data);
            $syncData = $this->formatSyncData($data['materials'] ?? []);

            $this->repo->syncMaterials($product, $syncData);

            return $product;
        });
    }

    public function deleteProduct(int $id): void
    {
        DB::transaction(function () use ($id): void {
            $product = $this->repo->findProduct($id);

            $this->repo->detachMaterials($product);
            $this->repo->deleteProduct($product);
        });
    }

    /**
     * @param  array<int, array<string, mixed>>  $materials
     * @return array<int, array{usage_qty: mixed}>
     */
    private function formatSyncData(array $materials): array
    {
        $syncData = [];
        foreach ($materials as $material) {
            if (! empty($material['inventory_id']) && ! empty($material['usage_qty'])) {
                $syncData[$material['inventory_id']] = ['usage_qty' => $material['usage_qty']];
            }
        }

        if (empty($syncData)) {
            throw new Exception('Minimal satu bahan recipe wajib diisi agar inventory forecasting berjalan.');
        }

        return $syncData;
    }
}
