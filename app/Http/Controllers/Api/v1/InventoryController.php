<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreInventoryRequest;
use App\Http\Requests\Api\V1\UpdateStockRequest;
use App\Services\InventoryService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class InventoryController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly InventoryService $service) {}

    public function getAlerts(): JsonResponse
    {
        $data = $this->service->getInventoryAlerts();

        return $this->successResponse($data);
    }

    public function getInventoryList(): JsonResponse
    {
        $data = $this->service->getInventoryAlerts()['inventory_alerts'];

        return $this->successResponse($data);
    }

    public function updateStock(UpdateStockRequest $request): JsonResponse
    {
        $item = $this->service->addManualStock($request->inventoryId(), $request->addedStock());

        return $this->successResponse(
            $item,
            "Berhasil menambahkan {$request->addedStock()} ke stok {$item->item_name}"
        );
    }

    public function store(StoreInventoryRequest $request): JsonResponse
    {
        $item = $this->service->createNewItem($request->inventoryData());

        return $this->successResponse($item, 'Material baru berhasil didaftarkan.', 201);
    }
}
