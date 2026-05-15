<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Contracts\TransactionImportRepositoryInterface;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class EloquentTransactionImportRepository implements TransactionImportRepositoryInterface
{
    public function saveSimpleTransactions(array $transactions): int
    {
        $now = now();
        $rows = array_map(fn ($transaction) => [
            'receipt_no' => $transaction['receipt_no'],
            'trx_date' => $transaction['trx_date'],
            'total_amount' => $transaction['total_amount'],
            'total_cogs' => 0,
            'net_profit' => $transaction['total_amount'],
            'created_at' => $transaction['trx_date'],
            'updated_at' => $now,
        ], $transactions);

        DB::table('transactions')->insert($rows);
        Cache::flush();

        return count($rows);
    }

    public function saveItemizedTransactions(array $transactions): int
    {
        $productNames = collect($transactions)
            ->flatMap(fn ($transaction) => array_column($transaction['items'], 'product_name'))
            ->unique()
            ->values()
            ->all();

        $products = DB::table('products')
            ->whereIn('name', $productNames)
            ->get(['id', 'name', 'cogs'])
            ->keyBy('name');

        $missingProducts = array_values(array_diff($productNames, $products->keys()->all()));
        if (!empty($missingProducts)) {
            throw new Exception('Produk tidak ditemukan di master data: ' . implode(', ', $missingProducts));
        }

        DB::transaction(function () use ($transactions, $products) {
            foreach ($transactions as $transaction) {
                $trxDate = Carbon::parse($transaction['trx_date']);
                $totalAmount = array_sum(array_column($transaction['items'], 'subtotal'));
                $totalCogs = 0;

                foreach ($transaction['items'] as $item) {
                    $product = $products[$item['product_name']];
                    $totalCogs += ((float) $product->cogs) * (int) $item['qty'];
                }

                $transactionId = DB::table('transactions')->insertGetId([
                    'receipt_no' => $transaction['receipt_no'],
                    'trx_date' => $trxDate,
                    'total_amount' => $totalAmount,
                    'total_cogs' => $totalCogs,
                    'net_profit' => $totalAmount - $totalCogs,
                    'created_at' => $trxDate,
                    'updated_at' => now(),
                ]);

                $details = array_map(function ($item) use ($transactionId, $products, $trxDate) {
                    $product = $products[$item['product_name']];
                    $qty = (int) $item['qty'];

                    return [
                        'transaction_id' => $transactionId,
                        'product_id' => $product->id,
                        'qty' => $qty,
                        'subtotal' => (float) $item['subtotal'],
                        'subtotal_cogs' => ((float) $product->cogs) * $qty,
                        'created_at' => $trxDate,
                        'updated_at' => now(),
                    ];
                }, $transaction['items']);

                DB::table('transaction_details')->insert($details);
            }
        });
        Cache::flush();

        return count($transactions);
    }
}
