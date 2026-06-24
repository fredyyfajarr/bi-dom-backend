<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryWasteLog extends Model
{
    use HasFactory;

    protected $table = 'inventory_waste_logs';

    protected $fillable = [
        'inventory_id',
        'qty_wasted',
        'cost_per_unit',
        'total_loss',
        'reason',
        'logged_at',
    ];

    protected $casts = [
        'logged_at' => 'datetime',
    ];

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class, 'inventory_id');
    }
}
