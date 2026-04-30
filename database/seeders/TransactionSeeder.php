<?php

namespace Database\Seeders;

use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema; // 1. Tambahkan import Schema ini

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        // 2. Matikan pengecekan foreign key sementara
        Schema::disableForeignKeyConstraints();

        // 3. Truncate tabel (Child dulu, baru Parent)
        TransactionDetail::truncate();
        Transaction::truncate();

        // 4. Nyalakan kembali pengecekan foreign key
        Schema::enableForeignKeyConstraints();

        $products = Product::all();
        if ($products->isEmpty()) {
            $this->command->info('Produk kosong. Harap jalankan ProductSeeder terlebih dahulu.');
            return;
        }

        $startDate = Carbon::now()->subMonths(3);
        $endDate = Carbon::now();

        for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
            $dailyCount = rand(5, 15);

            for ($i = 0; $i < $dailyCount; $i++) {
                $transaction = Transaction::create([
                    'receipt_no' => 'DOM-' . $date->format('Ymd') . '-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                    'trx_date' => $date->copy()->addHours(rand(10, 22))->addMinutes(rand(0, 59)),
                    'total_amount' => 0,
                ]);

                $itemsCount = rand(1, 4);
                $totalAmount = 0;

                for ($j = 0; $j < $itemsCount; $j++) {
                    $product = $products->random();
                    $qty = rand(1, 3);
                    $subtotal = $product->price * $qty;
                    $totalAmount += $subtotal;

                    TransactionDetail::create([
                        'transaction_id' => $transaction->id,
                        'product_id' => $product->id,
                        'qty' => $qty,
                        'subtotal' => $subtotal
                    ]);
                }

                $transaction->update(['total_amount' => $totalAmount]);
            }
        }
    }
}
