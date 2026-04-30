<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_no')->unique();
            $table->decimal('total_amount', 15, 2);
            $table->dateTime('trx_date')->index(); // Index penting untuk filter tanggal di BI
            $table->timestamps();
        });
}

    public function down(): void {
        Schema::dropIfExists('transactions');
    }
};
