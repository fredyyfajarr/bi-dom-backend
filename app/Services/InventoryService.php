<?php

namespace App\Services;

use App\Application\Inventory\GetInventoryAlerts;
use App\Repositories\InventoryRepository;
use Illuminate\Support\Facades\Cache;

class InventoryService
{
    public function __construct(
        private readonly InventoryRepository $repo,
        private readonly GetInventoryAlerts $getInventoryAlerts,
    )
    {
    }

    public function getInventoryAlerts()
    {
        return $this->getInventoryAlerts->execute();
    }

    public function addManualStock($id, $quantity)
    {
        $item = $this->repo->addStock($id, $quantity);

        // Hancurkan cache agar data alert inventory diperbarui
        Cache::flush();

        return $item;
    }

    public function createNewItem(array $data)
    {
        return $this->repo->createItem($data);
    }
}
