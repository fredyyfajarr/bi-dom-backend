<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    protected string $revenueCol = 'transaction_details.subtotal';

    public function getAllInvoices(
        string $search,
        string $sortBy,
        string $sortDir,
        string $filterDate,
        int $perPage = 15,
    ): LengthAwarePaginator {
        $query = DB::table('transactions')
            ->leftJoin('transaction_details', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->select(
                'transactions.id',
                'transactions.receipt_no',
                'transactions.trx_date',
                'transactions.payment_method',
                DB::raw('transactions.trx_date as created_at'),
                DB::raw("COALESCE(SUM({$this->revenueCol}), transactions.total_amount) as total_amount")
            )
            ->groupBy('transactions.id', 'transactions.receipt_no', 'transactions.trx_date', 'transactions.payment_method', 'transactions.total_amount');

        if (! empty($search)) {
            $query->where('transactions.receipt_no', 'like', "%{$search}%");
        }

        $now = Carbon::now();
        if ($filterDate === 'today') {
            $query->whereDate('transactions.trx_date', $now->toDateString());
        } elseif ($filterDate === 'this_month') {
            $query->whereMonth('transactions.trx_date', $now->month)
                ->whereYear('transactions.trx_date', $now->year);
        } elseif ($filterDate === 'this_year') {
            $query->whereYear('transactions.trx_date', $now->year);
        }

        $allowedSorts = ['id', 'receipt_no', 'created_at', 'trx_date', 'payment_method', 'total_amount'];
        $sortBy = in_array($sortBy, $allowedSorts, true) ? $sortBy : 'trx_date';
        $sortDir = strtolower($sortDir) === 'asc' ? 'asc' : 'desc';

        if ($sortBy === 'total_amount') {
            $query->orderByRaw("total_amount {$sortDir}");
        } else {
            $sortBy = $sortBy === 'created_at' ? 'trx_date' : $sortBy;
            $query->orderBy('transactions.'.$sortBy, $sortDir);
        }

        return $query->paginate($perPage);
    }

    /**
     * @return array{transaction: object, items: Collection<int, object>}|null
     */
    public function getInvoiceDetail(int $id): ?array
    {
        $transaction = DB::table('transactions')->where('id', $id)->first();
        if (! $transaction) {
            return null;
        }

        $items = DB::table('transaction_details')
            ->leftJoin('products', 'transaction_details.product_id', '=', 'products.id')
            ->select(
                DB::raw('COALESCE(products.name, transaction_details.product_name) as name'),
                'transaction_details.qty',
                DB::raw('(transaction_details.subtotal / transaction_details.qty) as price'),
                'transaction_details.subtotal'
            )
            ->where('transaction_details.transaction_id', $id)
            ->get();

        return ['transaction' => $transaction, 'items' => $items];
    }
}
