<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Services\ProductService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    protected $service;

    // Inject Service via Constructor
    public function __construct(ProductService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $products = $this->service->getAllProducts();
        return response()->json(['success' => true, 'data' => $products]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'category_id' => 'required|integer',
            'price' => 'required|numeric',
            'materials' => 'array',
        ]);

        $this->service->saveProductAndRecipe($validated);

        return response()->json(['success' => true, 'message' => 'Product and Recipe saved!']);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'category_id' => 'required|integer',
            'price' => 'required|numeric',
            'materials' => 'array',
        ]);

        $this->service->updateProductAndRecipe($id, $validated);

        return response()->json(['success' => true, 'message' => 'Product and Recipe updated!']);
    }
}
