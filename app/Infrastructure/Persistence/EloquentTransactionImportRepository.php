<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Contracts\TransactionImportRepositoryInterface;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class EloquentTransactionImportRepository implements TransactionImportRepositoryInterface
{
    private const INSERT_CHUNK_SIZE = 500;

    public function getExistingReceiptNos(array $receiptNos): array
    {
        if (empty($receiptNos)) {
            return [];
        }

        return DB::table('transactions')
            ->whereIn('receipt_no', $receiptNos)
            ->pluck('receipt_no')
            ->all();
    }

    public function saveSimpleTransactions(array $transactions): int
    {
        $now = now();
        $rows = array_map(fn ($transaction) => [
            'receipt_no' => $transaction['receipt_no'],
            'trx_date' => $transaction['trx_date'],
            'payment_method' => $transaction['payment_method'] ?? 'CASH',
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
            ->get(['id', 'name', 'price', 'cogs'])
            ->keyBy('name');

        $missingProducts = array_values(array_diff($productNames, $products->keys()->all()));
        if (!empty($missingProducts)) {
            throw new Exception('Produk tidak ditemukan di master data: ' . implode(', ', $missingProducts));
        }

        DB::transaction(function () use ($transactions, $products) {
            $now = now();
            $transactionRows = [];
            $trxDatesByReceipt = [];

            foreach ($transactions as $transaction) {
                $trxDate = Carbon::parse($transaction['trx_date']);
                $trxDatesByReceipt[$transaction['receipt_no']] = $trxDate;
                $totalAmount = array_sum(array_column($transaction['items'], 'subtotal'));
                $totalCogs = 0;

                foreach ($transaction['items'] as $item) {
                    $product = $products[$item['product_name']];
                    $totalCogs += ((float) $product->cogs) * (int) $item['qty'];
                }

                $transactionRows[] = [
                    'receipt_no' => $transaction['receipt_no'],
                    'trx_date' => $trxDate,
                    'payment_method' => $transaction['payment_method'] ?? 'CASH',
                    'total_amount' => $totalAmount,
                    'total_cogs' => $totalCogs,
                    'net_profit' => $totalAmount - $totalCogs,
                    'created_at' => $trxDate,
                    'updated_at' => $now,
                ];
            }

            foreach (array_chunk($transactionRows, self::INSERT_CHUNK_SIZE) as $chunk) {
                DB::table('transactions')->insert($chunk);
            }

            $transactionIds = DB::table('transactions')
                ->whereIn('receipt_no', array_column($transactionRows, 'receipt_no'))
                ->pluck('id', 'receipt_no')
                ->all();

            $detailRows = [];
            $inventoryUsage = [];
            $recipeRows = DB::table('product_inventory')
                ->whereIn('product_id', $products->pluck('id')->all())
                ->get(['product_id', 'inventory_id', 'usage_qty'])
                ->groupBy('product_id');

            foreach ($transactions as $transaction) {
                $receiptNo = $transaction['receipt_no'];
                $transactionId = $transactionIds[$receiptNo] ?? throw new Exception("Transaksi {$receiptNo} gagal dipetakan setelah import.");
                $trxDate = $trxDatesByReceipt[$receiptNo];

                foreach ($transaction['items'] as $item) {
                    $product = $products[$item['product_name']];
                    $qty = (int) $item['qty'];

                    $detailRows[] = [
                        'transaction_id' => $transactionId,
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'product_price' => $product->price,
                        'qty' => $qty,
                        'subtotal' => (float) $item['subtotal'],
                        'subtotal_cogs' => ((float) $product->cogs) * $qty,
                        'created_at' => $trxDate,
                        'updated_at' => $now,
                    ];

                    foreach ($recipeRows->get($product->id, collect()) as $recipeRow) {
                        $inventoryUsage[$recipeRow->inventory_id] = ($inventoryUsage[$recipeRow->inventory_id] ?? 0)
                            + ((float) $recipeRow->usage_qty * $qty);
                    }
                }
            }

            foreach (array_chunk($detailRows, self::INSERT_CHUNK_SIZE) as $chunk) {
                DB::table('transaction_details')->insert($chunk);
            }

            foreach ($inventoryUsage as $inventoryId => $usedQuantity) {
                DB::table('inventories')
                    ->where('id', $inventoryId)
                    ->update([
                        'current_stock' => DB::raw('GREATEST(current_stock - '.$usedQuantity.', 0)'),
                        'updated_at' => $now,
                    ]);
            }
        });
        Cache::flush();

        return count($transactions);
    }
}
