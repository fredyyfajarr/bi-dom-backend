<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RealisticTransactionSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('Memulai generate belasan ribu data transaksi yang realistis...');

        // 1. KOSONGKAN DATA LAMA
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('transaction_details')->truncate();
        DB::table('transactions')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 2. AMBIL PRODUK OTOMATIS DARI DATABASE MAS FREDY
        $products = DB::table('products')->get();

        if ($products->isEmpty()) {
            $this->command->error('Tabel products Anda kosong! Silakan isi data master produk dulu.');
            return;
        }

        // 3. BUAT PELUANG LARIS (Weighted Array) OTOMATIS
        $productPool = [];
        foreach ($products as $product) {
            // Misal: Kategori 4 (Snack) dan Kategori 1 dibuat lebih laris / lebih sering muncul
            $weight = in_array($product->category_id, [1, 4]) ? 5 : 2;

            for ($k = 0; $k < $weight; $k++) {
                $productPool[] = $product; // Simpan seluruh data produk ke kolam undian
            }
        }

        // 4. SETUP RENTANG WAKTU
        $startDate = Carbon::create(2025, 1, 1);
        $endDate = Carbon::create(2026, 5, 10);

        $receiptCounter = 1;

        // 5. MULAI INSERT MASSAL
        DB::beginTransaction();

        while ($startDate->lte($endDate)) {
            $isWeekend = $startDate->isWeekend();
            $dailyTrxCount = $isWeekend ? rand(30, 60) : rand(10, 25);

            if (in_array($startDate->month, [3, 12])) {
                $dailyTrxCount += rand(10, 20);
            }

            for ($i = 0; $i < $dailyTrxCount; $i++) {
                $trxTime = $startDate->copy()->addHours(rand(9, 21))->addMinutes(rand(0, 59));

                $itemCount = rand(1, 4);
                $details = [];
                $totalAmount = 0;
                $totalCogs = 0;

                // LANGKAH A: Siapkan barang dari Kolam Undian
                for ($j = 0; $j < $itemCount; $j++) {
                    $randomProduct = $productPool[array_rand($productPool)];

                    $price = $randomProduct->price;
                    $baseCogs = $randomProduct->cogs;

                    // SIMULASI SUPPLY CHAIN ISSUE UNTUK DEMO
                    if ($startDate->month === 4 && $startDate->year === 2026) {
                        // April: Biaya modal naik drastis (Margin jadi ~15-18%)
                        $baseCogs = $price * 0.82; 
                    } elseif ($startDate->month === 5 && $startDate->year === 2026) {
                        // May: Biaya modal naik sedang (Margin jadi ~30-35%)
                        $baseCogs = $price * 0.65;
                    }

                    $qty = rand(1, 3);
                    $subtotal = $price * $qty;
                    $subtotalCogs = $baseCogs * $qty;

                    $totalAmount += $subtotal;
                    $totalCogs += $subtotalCogs;

                    $details[] = [
                        'product_id' => $randomProduct->id,
                        'qty' => $qty,
                        'subtotal' => $subtotal,
                        'subtotal_cogs' => $subtotalCogs,
                        'created_at' => $trxTime,
                        'updated_at' => $trxTime,
                    ];
                }

                $netProfit = $totalAmount - $totalCogs;

                // LANGKAH B: Buat Transaksi
                $trxId = DB::table('transactions')->insertGetId([
                    'receipt_no' => 'TRX-' . $trxTime->format('Ymd') . '-' . str_pad($receiptCounter++, 4, '0', STR_PAD_LEFT),
                    'total_amount' => $totalAmount,
                    'total_cogs' => $totalCogs,
                    'net_profit' => $netProfit,
                    'trx_date' => $trxTime,
                    'created_at' => $trxTime,
                    'updated_at' => $trxTime,
                ]);

                // LANGKAH C: Pasang ID Transaksi ke barang, lalu simpan ke database
                foreach ($details as &$detail) {
                    $detail['transaction_id'] = $trxId;
                }

                DB::table('transaction_details')->insert($details);
            }

            $startDate->addDay();
        }

        DB::commit();

        $this->command->info('SUKSES! Data Categories 4 dan lainnya sudah di-generate otomatis.');
    }
}
