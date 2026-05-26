<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
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
                DB::raw('transactions.trx_date as created_at'),
                DB::raw("COALESCE(SUM({$this->revenueCol}), transactions.total_amount) as total_amount")
            )
            ->groupBy('transactions.id', 'transactions.receipt_no', 'transactions.trx_date', 'transactions.total_amount');

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

        $allowedSorts = ['id', 'receipt_no', 'created_at', 'trx_date', 'total_amount'];
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
}
