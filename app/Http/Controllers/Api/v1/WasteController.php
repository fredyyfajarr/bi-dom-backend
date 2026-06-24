<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Services\InventoryService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WasteController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly InventoryService $service) {}

    public function index(): JsonResponse
    {
        $logs = $this->service->getWasteLogs();
        return $this->successResponse($logs);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'inventory_id' => 'required|exists:inventories,id',
            'qty_wasted' => 'required|numeric|min:0.0001',
            'reason' => 'nullable|string|max:50',
            'logged_at' => 'nullable|date',
        ]);

        $log = $this->service->storeWasteLog($validated);

        return $this->successResponse($log, 'Berhasil mencatat pembuangan bahan baku/waste.', 201);
    }
}
