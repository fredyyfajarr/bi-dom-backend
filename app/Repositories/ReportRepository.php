<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class ReportRepository
{
   public function getReportData($startDate)
    {
        // 1. Ambil KPI (Langsung dari tabel induk, query menjadi sangat ringan!)
        $kpi = DB::table('transactions')
            ->where('created_at', '>=', $startDate) // Sesuaikan dengan nama kolom tgl Anda
            ->selectRaw('
                SUM(total_amount) as revenue,
                SUM(total_cogs) as total_cogs,
                SUM(net_profit) as net_profit,
                COUNT(id) as trx_count
            ')->first();

        $revenue = (float) ($kpi->revenue ?? 0);
        $netProfit = (float) ($kpi->net_profit ?? 0);
        $profitMargin = $revenue > 0 ? ($netProfit / $revenue) * 100 : 0;

        // 2. Ambil Top Items (Tetap seperti sebelumnya)
        $topItems = DB::table('transaction_details')
            ->join('products', 'transaction_details.product_id', '=', 'products.id')
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->where('transactions.created_at', '>=', $startDate)
            ->select('products.name', DB::raw('SUM(transaction_details.qty) as total_qty'))
            ->groupBy('products.id', 'products.name')
            ->orderBy('total_qty', 'desc')
            ->limit(5)
            ->get();

        // 3. Ambil Market Basket (Bundling Recommendation)
        $marketBasket = DB::table('transaction_details as td1')
            ->join('transaction_details as td2', function ($join) {
                $join->on('td1.transaction_id', '=', 'td2.transaction_id')
                     ->whereRaw('td1.product_id < td2.product_id');
            })
            ->join('products as p1', 'td1.product_id', '=', 'p1.id')
            ->join('products as p2', 'td2.product_id', '=', 'p2.id')
            ->join('transactions as trx', 'td1.transaction_id', '=', 'trx.id')
            ->where('trx.created_at', '>=', $startDate)
            ->select(
                'p1.name as product_a',
                'p2.name as product_b',
                DB::raw('COUNT(DISTINCT td1.transaction_id) as times_bought_together')
            )
            ->groupBy('product_a', 'product_b')
            ->orderByDesc('times_bought_together')
            ->limit(5)
            ->get();

        // Lempar semua data ke Service -> Blade
        return [
            'revenue' => $revenue,
            'net_profit' => $netProfit,
            'profit_margin' => round($profitMargin, 1),
            'trx_count' => $kpi->trx_count ?? 0,
            'top_items' => $topItems,
            'market_basket' => $marketBasket
        ];
    }
}
