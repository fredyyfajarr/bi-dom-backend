<?php

namespace App\Repositories;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportRepository
{
    private function applyCategoryFilter(mixed $query, ?int $categoryId, string $column = 'products.category_id'): mixed
    {
        if ($categoryId) {
            $query->where($column, $categoryId);
        }

        return $query;
    }

    public function getCategoryName(int $categoryId): ?string
    {
        return DB::table('categories')->where('id', $categoryId)->value('name');
    }

    /**
     * @return array<string, mixed>
     */
    public function getReportData(Carbon $startDate, Carbon $endDate, array $excludeCategories = [], ?int $categoryId = null): array
    {
        if (!empty($excludeCategories) || $categoryId) {
            $query = DB::table('transaction_details')
                ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
                ->join('products', 'transaction_details.product_id', '=', 'products.id')
                ->whereBetween('transactions.trx_date', [$startDate, $endDate]);

            if (!empty($excludeCategories)) {
                $query->whereNotIn('products.category_id', $excludeCategories);
            }
            $this->applyCategoryFilter($query, $categoryId);

            $kpi = $query->selectRaw('
                    SUM(transaction_details.subtotal) as revenue,
                    SUM(transaction_details.subtotal_cogs) as total_cogs,
                    SUM(transaction_details.subtotal - transaction_details.subtotal_cogs) as net_profit,
                    COUNT(DISTINCT transactions.id) as trx_count
                ')->first();
        } else {
            $kpi = DB::table('transactions')
                ->whereBetween('trx_date', [$startDate, $endDate])
                ->selectRaw('
                    SUM(total_amount) as revenue,
                    SUM(total_cogs) as total_cogs,
                    SUM(net_profit) as net_profit,
                    COUNT(id) as trx_count
                ')->first();
        }

        $revenue = (float) ($kpi->revenue ?? 0);
        $netProfit = (float) ($kpi->net_profit ?? 0);
        $profitMargin = $revenue > 0 ? ($netProfit / $revenue) * 100 : 0;

        $topItems = DB::table('transaction_details')
            ->leftJoin('products', 'transaction_details.product_id', '=', 'products.id')
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->whereBetween('transactions.trx_date', [$startDate, $endDate]);

        if (!empty($excludeCategories)) {
            $topItems->whereNotIn('products.category_id', $excludeCategories);
        }
        $this->applyCategoryFilter($topItems, $categoryId);

        $topItems = $topItems
            ->select(
                DB::raw('COALESCE(products.name, transaction_details.product_name) as name'),
                DB::raw('SUM(transaction_details.qty) as total_qty')
            )
            ->groupBy('transaction_details.product_id', DB::raw('COALESCE(products.name, transaction_details.product_name)'))
            ->orderBy('total_qty', 'desc')
            ->limit(5)
            ->get();

        $marketBasket = DB::table('transaction_details as td1')
            ->join('transaction_details as td2', function ($join) {
                $join->on('td1.transaction_id', '=', 'td2.transaction_id')
                    ->whereRaw('td1.product_id < td2.product_id');
            })
            ->leftJoin('products as p1', 'td1.product_id', '=', 'p1.id')
            ->leftJoin('products as p2', 'td2.product_id', '=', 'p2.id')
            ->join('transactions as trx', 'td1.transaction_id', '=', 'trx.id')
            ->whereBetween('trx.trx_date', [$startDate, $endDate]);

        if (!empty($excludeCategories)) {
            $marketBasket->where(function ($inner) use ($excludeCategories) {
                $inner->whereNotIn('p1.category_id', $excludeCategories)
                    ->whereNotIn('p2.category_id', $excludeCategories);
            });
        }

        if ($categoryId) {
            $marketBasket->where(function ($inner) use ($categoryId) {
                $inner->where('p1.category_id', $categoryId)
                    ->orWhere('p2.category_id', $categoryId);
            });
        }

        $marketBasket = $marketBasket
            ->select(
                DB::raw('COALESCE(p1.name, td1.product_name) as product_a'),
                DB::raw('COALESCE(p2.name, td2.product_name) as product_b'),
                DB::raw('COUNT(DISTINCT td1.transaction_id) as times_bought_together')
            )
            ->groupBy('product_a', 'product_b')
            ->orderByDesc('times_bought_together')
            ->limit(5)
            ->get();

        $wasteLoss = (float) DB::table('inventory_waste_logs')
            ->whereBetween('logged_at', [$startDate, $endDate])
            ->sum('total_loss');

        $opnameLossVal = DB::table('stock_opnames')
            ->whereBetween('adjusted_at', [$startDate, $endDate])
            ->where('total_adjustment_value', '<', 0)
            ->sum('total_adjustment_value');
        $opnameLoss = abs((float) $opnameLossVal);

        $actualNetProfit = $netProfit - $wasteLoss - $opnameLoss;
        $actualProfitMargin = $revenue > 0 ? ($actualNetProfit / $revenue) * 100 : 0;

        return [
            'revenue' => $revenue,
            'total_cogs' => (float) ($kpi->total_cogs ?? 0),
            'net_profit' => $netProfit,
            'profit_margin' => round($profitMargin, 1),
            'trx_count' => $kpi->trx_count ?? 0,
            'top_items' => $topItems,
            'market_basket' => $marketBasket,
            'waste_loss' => $wasteLoss,
            'opname_loss' => $opnameLoss,
            'actual_net_profit' => $actualNetProfit,
            'actual_profit_margin' => round($actualProfitMargin, 1),
        ];
    }
}
