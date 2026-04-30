<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class DashboardRepository
{
    protected $revenueCol = 'transaction_details.subtotal';
    protected $qtyCol = 'transaction_details.qty';

    public function getAvailableYears()
    {
        return DB::table('transactions')->selectRaw('YEAR(created_at) as year')->distinct()->orderBy('year', 'desc')->pluck('year')->toArray();
    }

    public function getCategoriesList()
    {
        return DB::table('categories')->select('id', 'name')->get();
    }

    public function getTotalTransactionsSince($startDate)
    {
        return DB::table('transactions')->where('created_at', '>=', $startDate)->count('id');
    }

    public function getAllInventories()
    {
        return DB::table('inventories')->get();
    }

    public function getKpiStats($startDate, $endDate, $excludeCategories = [])
    {
        if (empty($excludeCategories)) {
            return DB::table('transactions')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw('SUM(total_amount) as total_revenue, SUM(total_cogs) as total_cogs, SUM(net_profit) as net_profit, COUNT(id) as total_count')
                ->first();
        } else {
            return DB::table('transaction_details')
                ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
                ->join('products', 'transaction_details.product_id', '=', 'products.id')
                ->whereBetween('transactions.created_at', [$startDate, $endDate])
                ->whereNotIn('products.category_id', $excludeCategories)
                ->selectRaw('SUM(transaction_details.subtotal) as total_revenue, SUM(transaction_details.subtotal_cogs) as total_cogs, COUNT(DISTINCT transactions.id) as total_count')
                ->first();
        }
    }

    public function getChartData($startDate, $endDate, $period)
    {
        return DB::table('transaction_details')
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->join('products', 'transaction_details.product_id', '=', 'products.id')
            ->whereBetween('transactions.created_at', [$startDate, $endDate])
            ->select(
                'products.category_id',
                DB::raw("SUM({$this->revenueCol}) as total_revenue"),
                DB::raw($period === 'year' ? 'MONTH(transactions.created_at) as time_unit' : 'DAY(transactions.created_at) as time_unit')
            )
            ->groupBy('products.category_id', 'time_unit')->get();
    }

    public function getLatestTransactions($startDate, $endDate, $excludeCategories = [])
    {
        $query = DB::table('transaction_details')
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->join('products', 'transaction_details.product_id', '=', 'products.id')
            ->whereBetween('transactions.created_at', [$startDate, $endDate]);

        if (!empty($excludeCategories)) {
            $query->whereNotIn('products.category_id', $excludeCategories);
        }

        return $query->select('transactions.id', 'transactions.receipt_no', DB::raw("SUM({$this->revenueCol}) as total_amount"))
            ->groupBy('transactions.id', 'transactions.receipt_no')
            ->orderByRaw('MAX(transactions.created_at) DESC')
            ->limit(10)->get();
    }

    public function getTopProducts($startDate, $endDate, $excludeCategories = [])
    {
        $query = DB::table('transaction_details')
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->join('products', 'transaction_details.product_id', '=', 'products.id')
            ->whereBetween('transactions.created_at', [$startDate, $endDate]);

        if (!empty($excludeCategories)) {
            $query->whereNotIn('products.category_id', $excludeCategories);
        }

        return $query->select('products.name', DB::raw("SUM({$this->qtyCol}) as total_qty"), DB::raw("SUM({$this->revenueCol}) as total_revenue"))
            ->groupBy('products.id', 'products.name')
            ->orderBy('total_qty', 'desc')
            ->limit(5)->get();
    }

    public function getTransactionById($id)
    {
        return DB::table('transactions')->where('id', $id)->first();
    }

    public function getTransactionDetails($transactionId)
    {
        return DB::table('transaction_details')
            ->join('products', 'transaction_details.product_id', '=', 'products.id')
            ->select('products.name', 'transaction_details.qty', DB::raw('(transaction_details.subtotal / transaction_details.qty) as price'), 'transaction_details.subtotal')
            ->where('transaction_details.transaction_id', $transactionId)
            ->get();
    }

    public function getCategoryProportions($startDate, $endDate, $excludeCategories = [])
    {
        $query = DB::table('transaction_details')
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->join('products', 'transaction_details.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->whereBetween('transactions.created_at', [$startDate, $endDate]);

        if (!empty($excludeCategories)) {
            $query->whereNotIn('products.category_id', $excludeCategories);
        }

        return $query->select('categories.name as label', DB::raw("SUM({$this->qtyCol}) as value"))
            ->groupBy('categories.id', 'categories.name')
            ->get();
    }

    public function getDailyRevenue($startDate, $endDate)
    {
        return DB::table('transactions')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DAYNAME(created_at) as day_name, DAYOFWEEK(created_at) as day_num, SUM(total_amount) as total')
            ->groupBy('day_name', 'day_num')
            ->orderBy('day_num')
            ->get();
    }

    public function getPeakHours($startDate, $endDate)
    {
        return DB::table('transactions')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DAYNAME(created_at) as day_name, HOUR(created_at) as hour, COUNT(id) as total_trx')
            ->groupBy('day_name', 'hour')
            ->get();
    }


    public function getStackedCategoryTrend($startDate, $endDate, $period)
    {
        $timeUnit = $period === 'year' ? 'MONTH(transactions.created_at)' : 'DAY(transactions.created_at)';
        return DB::table('transaction_details')
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->join('products', 'transaction_details.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->whereBetween('transactions.created_at', [$startDate, $endDate])
            ->selectRaw("categories.name as category_name, {$timeUnit} as time_unit, SUM({$this->revenueCol}) as total_revenue")
            ->groupBy('categories.id', 'categories.name', 'time_unit')
            ->get();
    }

    public function getMarketBasket($startDate, $endDate)
    {
        return DB::table('transaction_details as td1')
            ->join('transaction_details as td2', function ($join) {
                $join->on('td1.transaction_id', '=', 'td2.transaction_id')
                     ->whereRaw('td1.product_id < td2.product_id');
            })
            ->join('products as p1', 'td1.product_id', '=', 'p1.id')
            ->join('products as p2', 'td2.product_id', '=', 'p2.id')
            ->join('transactions as trx', 'td1.transaction_id', '=', 'trx.id')
            ->whereBetween('trx.created_at', [$startDate, $endDate])
            ->select('p1.name as product_a', 'p2.name as product_b', DB::raw('COUNT(DISTINCT td1.transaction_id) as times_bought_together'))
            ->groupBy('product_a', 'product_b')
            ->orderByDesc('times_bought_together')
            ->limit(5)
            ->get();
    }

    public function getPeakHourDrillDown($startDate, $endDate, $dayName, $hour)
    {
        // 1. Hitung total transaksi di jam & hari tersebut
        $trxCount = DB::table('transactions')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereRaw('DAYNAME(created_at) = ?', [$dayName])
            ->whereRaw('HOUR(created_at) = ?', [$hour])
            ->count('id');

        // 2. Ambil produk paling laku di jam & hari tersebut
        $topItems = DB::table('transaction_details')
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->join('products', 'transaction_details.product_id', '=', 'products.id')
            ->whereBetween('transactions.created_at', [$startDate, $endDate])
            ->whereRaw('DAYNAME(transactions.created_at) = ?', [$dayName])
            ->whereRaw('HOUR(transactions.created_at) = ?', [$hour])
            ->select('products.name', DB::raw("SUM(transaction_details.qty) as total_qty"))
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_qty')
            ->limit(3)
            ->get();

        // 3. Market Basket KHUSUS untuk jam & hari tersebut!
        $marketBasket = DB::table('transaction_details as td1')
            ->join('transaction_details as td2', function ($join) {
                $join->on('td1.transaction_id', '=', 'td2.transaction_id')
                     ->whereRaw('td1.product_id < td2.product_id');
            })
            ->join('products as p1', 'td1.product_id', '=', 'p1.id')
            ->join('products as p2', 'td2.product_id', '=', 'p2.id')
            ->join('transactions as trx', 'td1.transaction_id', '=', 'trx.id')
            ->whereBetween('trx.created_at', [$startDate, $endDate])
            ->whereRaw('DAYNAME(trx.created_at) = ?', [$dayName])
            ->whereRaw('HOUR(trx.created_at) = ?', [$hour])
            ->select('p1.name as product_a', 'p2.name as product_b', DB::raw('COUNT(DISTINCT td1.transaction_id) as times_bought_together'))
            ->groupBy('product_a', 'product_b')
            ->orderByDesc('times_bought_together')
            ->limit(2)
            ->get();

        return [
            'total_trx' => $trxCount,
            'top_items' => $topItems,
            'market_basket' => $marketBasket
        ];
    }
}
