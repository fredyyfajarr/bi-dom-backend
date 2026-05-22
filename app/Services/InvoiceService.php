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
                'transactions.created_at',
                DB::raw("COALESCE(SUM({$this->revenueCol}), transactions.total_amount) as total_amount")
            )
            ->groupBy('transactions.id', 'transactions.receipt_no', 'transactions.created_at', 'transactions.total_amount');

        if (! empty($search)) {
            $query->where('transactions.receipt_no', 'like', "%{$search}%");
        }

        $now = Carbon::now();
        if ($filterDate === 'today') {
            $query->whereDate('transactions.created_at', $now->toDateString());
        } elseif ($filterDate === 'this_month') {
            $query->whereMonth('transactions.created_at', $now->month)
                ->whereYear('transactions.created_at', $now->year);
        } elseif ($filterDate === 'this_year') {
            $query->whereYear('transactions.created_at', $now->year);
        }

        $allowedSorts = ['id', 'receipt_no', 'created_at', 'total_amount'];
        $sortBy = in_array($sortBy, $allowedSorts) ? $sortBy : 'created_at';
        $sortDir = strtolower($sortDir) === 'asc' ? 'asc' : 'desc';

        if ($sortBy === 'total_amount') {
            $query->orderByRaw("total_amount {$sortDir}");
        } else {
            $query->orderBy('transactions.'.$sortBy, $sortDir);
        }

        return $query->paginate($perPage);
    }
}
