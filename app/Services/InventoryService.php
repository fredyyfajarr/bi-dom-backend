<?php

namespace App\Services;

use App\Application\Inventory\GetInventoryAlerts;
use App\Models\Inventory;
use App\Models\InventoryWasteLog;
use App\Models\StockOpname;
use App\Repositories\InventoryRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    public function __construct(
        private readonly InventoryRepository $repo,
        private readonly GetInventoryAlerts $getInventoryAlerts,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function getInventoryAlerts(): array
    {
        return $this->getInventoryAlerts->execute();
    }

    public function addManualStock(int $id, float $quantity): Inventory
    {
        $item = $this->repo->addStock($id, $quantity);

        Cache::flush();

        return $item;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createNewItem(array $data): Inventory
    {
        return $this->repo->createItem($data);
    }

    public function getWasteLogs()
    {
        return InventoryWasteLog::with('inventory')
            ->orderBy('logged_at', 'desc')
            ->get();
    }

    public function storeWasteLog(array $data): InventoryWasteLog
    {
        return DB::transaction(function () use ($data) {
            $inventory = Inventory::findOrFail($data['inventory_id']);
            $qtyWasted = (float) $data['qty_wasted'];
            $unitCost = (float) $inventory->unit_cost;
            $totalLoss = $qtyWasted * $unitCost;

            $log = InventoryWasteLog::create([
                'inventory_id' => $inventory->id,
                'qty_wasted' => $qtyWasted,
                'cost_per_unit' => $unitCost,
                'total_loss' => $totalLoss,
                'reason' => $data['reason'] ?? 'OTHER',
                'logged_at' => $data['logged_at'] ?? now(),
            ]);

            // Decrement stock, ensuring it doesn't go below 0
            $inventory->current_stock = max(0, $inventory->current_stock - $qtyWasted);
            $inventory->save();

            Cache::flush();

            return $log;
        });
    }

    public function getStockOpnames()
    {
        return StockOpname::with('inventory')
            ->orderBy('adjusted_at', 'desc')
            ->get();
    }

    public function storeStockOpname(array $data): StockOpname
    {
        return DB::transaction(function () use ($data) {
            $inventory = Inventory::findOrFail($data['inventory_id']);
            $physicalQty = (float) $data['physical_qty'];
            $systemQty = (float) $inventory->current_stock;
            $discrepancy = $physicalQty - $systemQty;
            $unitCost = (float) $inventory->unit_cost;
            $totalAdjustment = $discrepancy * $unitCost;

            $opname = StockOpname::create([
                'inventory_id' => $inventory->id,
                'system_qty' => $systemQty,
                'physical_qty' => $physicalQty,
                'discrepancy' => $discrepancy,
                'cost_per_unit' => $unitCost,
                'total_adjustment_value' => $totalAdjustment,
                'adjusted_at' => $data['adjusted_at'] ?? now(),
            ]);

            // Set current stock to physical qty
            $inventory->current_stock = $physicalQty;
            $inventory->save();

            Cache::flush();

            return $opname;
        });
    }
}
