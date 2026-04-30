<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    protected $table = 'inventories';
    protected $fillable = ['item_name', 'unit', 'current_stock', 'min_stock', 'usage_per_trx'];

    // Jembatan balik untuk mengetahui bahan ini dipakai di produk apa saja
    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_inventory')
                    ->withPivot('usage_qty')
                    ->withTimestamps();
    }
}
