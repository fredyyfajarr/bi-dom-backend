<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreProductRequest;
use App\Http\Requests\Api\V1\UpdateProductRequest;
use App\Services\ProductService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly ProductService $service) {}

    public function index(): JsonResponse
    {
        try {
            $products = $this->service->getAllProducts();

            return $this->successResponse($products);
        } catch (Exception $e) {
            return $this->errorResponse('Gagal mengambil data produk: '.$e->getMessage(), 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            return $this->successResponse($this->service->findProduct($id));
        } catch (Exception $e) {
            return $this->errorResponse('Produk tidak ditemukan: '.$e->getMessage(), 404);
        }
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        try {
            $this->service->saveProductAndRecipe($request->productData());

            return $this->successResponse(null, 'Product and Recipe saved!');
        } catch (Exception $e) {
            return $this->errorResponse('Gagal menyimpan produk: '.$e->getMessage(), 500);
        }
    }

    public function update(UpdateProductRequest $request, int $id): JsonResponse
    {
        try {
            $this->service->updateProductAndRecipe($id, $request->productData());

            return $this->successResponse(null, 'Product and Recipe updated!');
        } catch (Exception $e) {
            return $this->errorResponse('Gagal mengupdate produk: '.$e->getMessage(), 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->service->deleteProduct($id);

            return $this->successResponse(null, 'Product deleted!');
        } catch (Exception $e) {
            return $this->errorResponse('Gagal menghapus produk: '.$e->getMessage(), 500);
        }
    }
}
