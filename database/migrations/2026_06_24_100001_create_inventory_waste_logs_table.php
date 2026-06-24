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
        Schema::create('inventory_waste_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_id')->constrained('inventories')->onDelete('cascade');
            $table->decimal('qty_wasted', 12, 4);
            $table->decimal('cost_per_unit', 15, 2);
            $table->decimal('total_loss', 15, 2);
            $table->string('reason'); // e.g. EXPIRED, SPILLED, REMAKE_ORDER, OTHER
            $table->timestamp('logged_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_waste_logs');
    }
};
