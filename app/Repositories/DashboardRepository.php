<?php

namespace App\Repositories;

use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class DashboardRepository
{
    public function getKpiStats($startDate)
    {
        $query = Transaction::where('trx_date', '>=', $startDate);

        return [
            'revenue' => (float) $query->sum('total_amount'),
            'transaction_count' => $query->count(),
        ];
    }

    public function getSalesChart($startDate)
    {
        $transactions = Transaction::select(
            DB::raw('DATE(trx_date) as date'),
            DB::raw('SUM(total_amount) as total')
        )
        ->where('trx_date', '>=', $startDate)
        ->groupBy('date')
        ->orderBy('date', 'asc')
        ->get();

        return [
            'labels' => $transactions->pluck('date')->toArray(),
            'data' => $transactions->pluck('total')->toArray(),
        ];
    }

    public function getLatestTransactions($startDate)
    {
        return Transaction::where('trx_date', '>=', $startDate)
            ->orderBy('trx_date', 'desc')
            ->take(5)
            ->get();
    }

    public function getTopProducts($startDate)
    {
        // Logika untuk mengambil produk yang paling banyak terjual
        return DB::table('transaction_details')
            ->join('products', 'transaction_details.product_id', '=', 'products.id')
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->select(
                'products.name',
                DB::raw('SUM(transaction_details.qty) as total_qty'),
                DB::raw('SUM(transaction_details.subtotal) as total_revenue')
            )
            // ->where('transactions.trx_date', '>=', $startDate)
            ->groupBy('products.id', 'products.name')
            ->orderBy('total_qty', 'desc')
            ->limit(5)
            ->get();
    }

    // Mengambil data utama struk
public function getTransactionById($id)
    {
        // Menggunakan DB table agar tidak crash mencari Model
        return DB::table('transactions')->where('id', $id)->first();
    }

    public function getTransactionDetails($transactionId)
    {
        return DB::table('transaction_details')
            ->join('products', 'transaction_details.product_id', '=', 'products.id')
            ->select(
                'products.name',
                'transaction_details.qty',
                DB::raw('(transaction_details.subtotal / transaction_details.qty) as price'), // <--- Hitung mundur dari subtotal
                'transaction_details.subtotal'
            )
            ->where('transaction_details.transaction_id', $transactionId)
            ->get();
    }
}
