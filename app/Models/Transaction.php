<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Table('transactions')]
#[Fillable(['receipt_no', 'trx_date', 'payment_method', 'total_amount', 'total_cogs', 'net_profit'])]
class Transaction extends Model
{
    public function details(): HasMany
    {
        return $this->hasMany(TransactionDetail::class);
    }
}
