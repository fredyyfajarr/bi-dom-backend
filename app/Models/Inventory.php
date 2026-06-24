<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Table('inventories')]
#[Fillable(['item_name', 'unit', 'unit_cost', 'current_stock', 'min_stock', 'usage_per_trx'])]
class Inventory extends Model
{
    use HasFactory;

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_inventory')
            ->withPivot('usage_qty')
            ->withTimestamps();
    }
}
