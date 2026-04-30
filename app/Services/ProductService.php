<?php

namespace App\Services;

use App\Repositories\ProductRepository;
use Illuminate\Support\Facades\DB;

class ProductService
{
    protected $repo;

    public function __construct(ProductRepository $repo)
    {
        $this->repo = $repo;
    }

    public function getAllProducts()
    {
        return $this->repo->getAllProducts();
    }

    public function saveProductAndRecipe(array $data)
    {
        return DB::transaction(function () use ($data) {
            // 1. Simpan Produk
            $product = $this->repo->createProduct($data);

            // 2. Format & Simpan Resep
            $syncData = $this->formatSyncData($data['materials'] ?? []);
            $this->repo->syncMaterials($product, $syncData);

            return $product;
        });
    }

    public function updateProductAndRecipe($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            // 1. Update Produk
            $product = $this->repo->updateProduct($id, $data);

            // 2. Format & Simpan Resep (Otomatis merge/hapus yang lama)
            $syncData = $this->formatSyncData($data['materials'] ?? []);
            $this->repo->syncMaterials($product, $syncData);

            return $product;
        });
    }

    // Fungsi internal untuk merapikan array resep
    private function formatSyncData(array $materials)
    {
        $syncData = [];
        foreach ($materials as $material) {
            if (!empty($material['inventory_id']) && !empty($material['usage_qty'])) {
                $syncData[$material['inventory_id']] = ['usage_qty' => $material['usage_qty']];
            }
        }
        return $syncData;
    }
}
