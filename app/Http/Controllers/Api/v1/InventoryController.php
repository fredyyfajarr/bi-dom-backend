<?php

namespace App\Http\Controllers\Api\v1; // Pastikan namespace-nya Api\v1

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\InventoryService;
use App\Traits\ApiResponse;

class InventoryController extends Controller
{
    use ApiResponse; // Memanggil trait standarisasi JSON

    protected $service;

    public function __construct(InventoryService $service)
    {
        $this->service = $service;
    }

    // Mengambil data untuk halaman Inventory Alert
    public function getAlerts()
    {
        $data = $this->service->getInventoryAlerts();
        return $this->successResponse($data);
    }

    // Mengambil list bahan untuk dropdown form update stok
    public function getInventoryList()
    {
        // Kita bisa pakai langsung fungsi dari service karena sudah map ke repository
        $data = $this->service->getInventoryAlerts()['inventory_alerts'];
        return $this->successResponse($data);
    }

    // Menyimpan update stok manual dari kasir
    public function updateStock(Request $request)
    {
        $request->validate([
            'inventory_id' => 'required|exists:inventories,id',
            'added_stock' => 'required|numeric|min:0',
        ]);

        $item = $this->service->addManualStock($request->inventory_id, $request->added_stock);
        return $this->successResponse(
            $item,
            "Berhasil menambahkan $request->added_stock ke stok " . $item->item_name
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'item_name' => 'required|string|max:255',
            'unit' => 'required|string|max:50',
            'current_stock' => 'required|numeric|min:0',
            'min_stock' => 'required|numeric|min:0',
            'usage_per_trx' => 'required|numeric|min:0',
        ]);

        $item = $this->service->createNewItem($request->all());
        return $this->successResponse($item, 'Material baru berhasil didaftarkan.', 201);
    }
}
