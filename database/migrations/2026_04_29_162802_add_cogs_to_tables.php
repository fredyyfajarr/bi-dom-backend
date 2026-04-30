<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up()
{
    // Tambah kolom modal di tabel master produk
    Schema::table('products', function (Blueprint $table) {
        $table->decimal('cogs', 15, 2)->after('price')->default(0)->comment('Harga Modal/HPP');
    });

    // Tambah kolom total modal per barang di detail transaksi
    Schema::table('transaction_details', function (Blueprint $table) {
        $table->decimal('subtotal_cogs', 15, 2)->after('subtotal')->default(0)->comment('Modal x Qty');
    });

    // Tambah total modal dan laba bersih di struk induk
    Schema::table('transactions', function (Blueprint $table) {
        $table->decimal('total_cogs', 15, 2)->after('total_amount')->default(0)->comment('Total Modal se-Struk');
        $table->decimal('net_profit', 15, 2)->after('total_cogs')->default(0)->comment('Laba Bersih (Amount - COGS)');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tables', function (Blueprint $table) {
            //
        });
    }
};
