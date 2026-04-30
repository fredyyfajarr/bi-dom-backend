<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = ['receipt_no', 'trx_date', 'total_amount'];

    // Tambahkan relasi ini
    public function details() {
        return $this->hasMany(TransactionDetail::class);
    }
}
