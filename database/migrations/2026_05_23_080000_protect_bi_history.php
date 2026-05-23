<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add soft deletes to products
        Schema::table('products', function (Blueprint $table) {
            $table->softDeletes();
        });

        // 2. Add soft deletes to categories
        Schema::table('categories', function (Blueprint $table) {
            $table->softDeletes();
        });

        // 3. Add snapshot columns to transaction_details
        Schema::table('transaction_details', function (Blueprint $table) {
            $table->string('product_name')->nullable()->after('product_id');
            $table->decimal('product_price', 15, 2)->nullable()->after('product_name');
        });

        // 4. Backfill snapshot data from existing products
        DB::statement('
            UPDATE transaction_details td
            JOIN products p ON td.product_id = p.id
            SET td.product_name = p.name,
                td.product_price = p.price
        ');

        // 5. Change FK on transaction_details.product_id from CASCADE to RESTRICT
        Schema::table('transaction_details', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->foreign('product_id')
                  ->references('id')->on('products')
                  ->onDelete('restrict');
        });

        // 6. Change FK on products.category_id from CASCADE to RESTRICT
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->foreign('category_id')
                  ->references('id')->on('categories')
                  ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->foreign('category_id')
                  ->references('id')->on('categories')
                  ->onDelete('cascade');
        });

        Schema::table('transaction_details', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->foreign('product_id')
                  ->references('id')->on('products')
                  ->onDelete('cascade');
        });

        Schema::table('transaction_details', function (Blueprint $table) {
            $table->dropColumn(['product_name', 'product_price']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
