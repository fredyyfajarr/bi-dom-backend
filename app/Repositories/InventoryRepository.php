<?php

namespace App\Repositories;

use App\Models\Inventory;

class InventoryRepository
{
    // Fungsi untuk mengambil semua data
    public function getAllItems()
    {
        return Inventory::all();
    }

    // Fungsi BARU untuk menambah stok
    public function addStock($id, $addedQuantity)
    {
        $item = Inventory::findOrFail($id);

        // Menggunakan fitur bawaan Laravel untuk menambah angka
        $item->increment('current_stock', $addedQuantity);

        return $item->fresh(); // Mengembalikan data yang sudah terupdate
    }

    public function createItem(array $data)
    {
        return Inventory::create($data);
    }
}
