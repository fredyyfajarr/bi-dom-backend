<?php

namespace App\Repositories;

use App\Models\Inventory;
use Illuminate\Database\Eloquent\Collection;

class InventoryRepository
{
    /**
     * @return Collection<int, Inventory>
     */
    public function getAllItems(): Collection
    {
        return Inventory::all();
    }

    public function addStock(int $id, float $addedQuantity): Inventory
    {
        $item = Inventory::findOrFail($id);

        $item->increment('current_stock', $addedQuantity);

        return $item->fresh();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createItem(array $data): Inventory
    {
        return Inventory::create($data);
    }
}
