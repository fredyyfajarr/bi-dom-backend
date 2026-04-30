<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Services\ProductService;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use Exception;

class ProductController extends Controller
{
    use ApiResponse;

    protected $service;

    // Inject Service via Constructor
    public function __construct(ProductService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        try {
            $products = $this->service->getAllProducts();
            return $this->successResponse($products);
        } catch (Exception $e) {
            return $this->errorResponse('Gagal mengambil data produk: ' . $e->getMessage(), 500);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'category_id' => 'required|integer',
            'price' => 'required|numeric',
            'materials' => 'array',
        ]);

        try {
            $this->service->saveProductAndRecipe($validated);
            return $this->successResponse(null, 'Product and Recipe saved!');
        } catch (Exception $e) {
            return $this->errorResponse('Gagal menyimpan produk: ' . $e->getMessage(), 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'category_id' => 'required|integer',
            'price' => 'required|numeric',
            'materials' => 'array',
        ]);

        try {
            $this->service->updateProductAndRecipe($id, $validated);
            return $this->successResponse(null, 'Product and Recipe updated!');
        } catch (Exception $e) {
            return $this->errorResponse('Gagal mengupdate produk: ' . $e->getMessage(), 500);
        }
    }
}
