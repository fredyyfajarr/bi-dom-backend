<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InvoiceService
{
    // Gunakan kolom subtotal yang sudah terbukti aman (sama seperti di DashboardService)
    protected $revenueCol = 'transaction_details.subtotal';

    public function getAllInvoices($search, $sortBy, $sortDir, $filterDate, $perPage = 15)
    {
        $query = DB::table('transactions')
            ->leftJoin('transaction_details', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->select(
                'transactions.id',
                'transactions.receipt_no',
                'transactions.created_at',
                DB::raw("COALESCE(SUM({$this->revenueCol}), transactions.total_amount) as total_amount")
            )
            ->groupBy('transactions.id', 'transactions.receipt_no', 'transactions.created_at', 'transactions.total_amount');

        // 1. SEARCHING (Cari berdasarkan Nomor Resi)
        if (!empty($search)) {
            $query->where('transactions.receipt_no', 'like', "%{$search}%");
        }

        // 2. FILTER BY DATE (Hari/Bulan/Tahun)
        $now = Carbon::now();
        if ($filterDate === 'today') {
            $query->whereDate('transactions.created_at', $now->toDateString());
        } elseif ($filterDate === 'this_month') {
            $query->whereMonth('transactions.created_at', $now->month)
                  ->whereYear('transactions.created_at', $now->year);
        } elseif ($filterDate === 'this_year') {
            $query->whereYear('transactions.created_at', $now->year);
        }

        // 3. SORTING
        $allowedSorts = ['id', 'receipt_no', 'created_at', 'total_amount'];
        $sortBy = in_array($sortBy, $allowedSorts) ? $sortBy : 'created_at';
        $sortDir = strtolower($sortDir) === 'asc' ? 'asc' : 'desc';

        // Pengecualian khusus jika sorting berdasarkan hasil perhitungan subquery (total_amount)
        if ($sortBy === 'total_amount') {
            $query->orderByRaw("total_amount {$sortDir}");
        } else {
            $query->orderBy('transactions.' . $sortBy, $sortDir);
        }

        // 4. PAGINATION
        return $query->paginate($perPage);
    }
}
