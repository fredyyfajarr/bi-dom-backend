<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Services\InventoryService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StockOpnameController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly InventoryService $service) {}

    public function index(): JsonResponse
    {
        $logs = $this->service->getStockOpnames();
        return $this->successResponse($logs);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'inventory_id' => 'required|exists:inventories,id',
            'physical_qty' => 'required|numeric|min:0',
            'adjusted_at' => 'nullable|date',
        ]);

        $opname = $this->service->storeStockOpname($validated);

        return $this->successResponse($opname, 'Berhasil mencatat penyesuaian stok opname.', 201);
    }
}
