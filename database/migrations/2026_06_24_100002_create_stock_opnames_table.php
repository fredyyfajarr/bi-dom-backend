<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stock_opnames', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_id')->constrained('inventories')->onDelete('cascade');
            $table->decimal('system_qty', 12, 4);
            $table->decimal('physical_qty', 12, 4);
            $table->decimal('discrepancy', 12, 4); // physical_qty - system_qty
            $table->decimal('cost_per_unit', 15, 2);
            $table->decimal('total_adjustment_value', 15, 2); // discrepancy * cost_per_unit
            $table->timestamp('adjusted_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_opnames');
    }
};
