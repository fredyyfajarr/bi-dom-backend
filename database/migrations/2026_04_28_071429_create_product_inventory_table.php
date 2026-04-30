<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('product_inventory', function (Blueprint $table) {
            $table->id();
            // Menyambungkan ke tabel products yg sudah ada
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            // Menyambungkan ke tabel inventories yg sudah ada
            $table->foreignId('inventory_id')->constrained()->onDelete('cascade');
            // Jumlah gram/pcs bahan yang dipakai per 1 produk
            $table->decimal('usage_qty', 12, 4);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_inventory');
    }
};
