<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    // Sesuaikan dengan kolom migration Anda
    protected $fillable = ['category_id', 'name', 'price'];

    // Relasi ke Category
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Jembatan menuju bahan baku (Resep)
    public function materials()
    {
        return $this->belongsToMany(Inventory::class, 'product_inventory')
                    ->withPivot('usage_qty')
                    ->withTimestamps();
    }
}
