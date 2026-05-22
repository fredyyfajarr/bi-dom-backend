<?php

namespace App\Services;

use App\Application\Inventory\GetInventoryAlerts;
use App\Models\Inventory;
use App\Repositories\InventoryRepository;
use Illuminate\Support\Facades\Cache;

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
}
