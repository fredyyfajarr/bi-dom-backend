<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('transaction_details')]
#[Fillable(['transaction_id', 'product_id', 'product_name', 'product_price', 'qty', 'subtotal', 'subtotal_cogs'])]
class TransactionDetail extends Model
{
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
