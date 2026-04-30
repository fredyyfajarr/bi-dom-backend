<?php

namespace App\Services;

use App\Repositories\DashboardRepository;
use Carbon\Carbon;

class DashboardService
{
    protected $repo;

    public function __construct(DashboardRepository $repo)
    {
        $this->repo = $repo;
    }

    private function getDateRange($year, $period, $monthIndex)
    {
        if ($period === 'month' && $monthIndex !== null) {
            $month = $monthIndex + 1;
            $start = Carbon::create($year, $month, 1)->startOfMonth();
            $end = Carbon::create($year, $month, 1)->endOfMonth();
        } else {
            $start = Carbon::create($year, 1, 1)->startOfYear();
            $end = Carbon::create($year, 1, 1)->endOfYear();
        }
        return [$start, $end];
    }

    public function getAvailableYears()
    {
        $years = $this->repo->getAvailableYears();
        return empty($years) ? [(int) date('Y')] : $years;
    }

    public function getCategoriesList()
    {
        return $this->repo->getCategoriesList();
    }

    public function getLowStockProducts()
    {
        $startDate = Carbon::now()->subDays(30);
        $totalTrx30Days = $this->repo->getTotalTransactionsSince($startDate);

        $dailyAverage = $totalTrx30Days > 0 ? ($totalTrx30Days / 30) : 0;
        $totalForecastTrx = round($dailyAverage * 7);

        $inventories = $this->repo->getAllInventories();
        $criticalItems = [];

        foreach ($inventories as $item) {
            $predictedUsage = $totalForecastTrx * $item->usage_per_trx;
            $sisaStok = $item->current_stock - $predictedUsage;

            if ($sisaStok <= $item->min_stock) {
                $criticalItems[] = [
                    'name'  => $item->item_name,
                    'stock' => $item->current_stock,
                    'unit'  => $item->unit
                ];
            }
        }

        return collect($criticalItems)->sortBy('stock')->values()->all();
    }

    public function getKpiStats($year, $period, $monthIndex, $excludeCategories = [])
    {
        [$startDate, $endDate] = $this->getDateRange($year, $period, $monthIndex);

        $data = $this->repo->getKpiStats($startDate, $endDate, $excludeCategories);

        $revenue = (float) ($data->total_revenue ?? 0);
        $cogs = (float) ($data->total_cogs ?? 0);
        $netProfit = $revenue - $cogs;
        $trxCount = (int) ($data->total_count ?? 0);

        $profitMargin = $revenue > 0 ? ($netProfit / $revenue) * 100 : 0;

        return [
            'revenue' => $revenue,
            'total_cogs' => $cogs,
            'net_profit' => $netProfit,
            'profit_margin' => round($profitMargin, 1),
            'transaction_count' => $trxCount
        ];
    }

    public function getSalesChart($year, $period = 'year', $monthIndex = null)
    {
        $labels = [];
        $dbCategories = $this->repo->getCategoriesList();
        $colorPalette = ['#dc2626', '#2563eb', '#16a34a', '#ca8a04', '#7c3aed', '#db2777'];

        if ($period === 'year') {
            $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            $title = "ANNUAL_CATEGORY_ANALYSIS: $year";
        } else {
            $actualMonth = $monthIndex + 1;
            $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $actualMonth, $year);
            $monthName = date('M', mktime(0, 0, 0, $actualMonth, 10));
            for ($i = 1; $i <= $daysInMonth; $i++) { $labels[] = $i . ' ' . $monthName; }
            $title = "DAILY_CATEGORY_ANALYSIS: " . date('F Y', mktime(0, 0, 0, $actualMonth, 10)) . " $year";
        }

        $datasets = [];
        foreach ($dbCategories as $index => $cat) {
            $color = $colorPalette[$index] ?? '#' . substr(md5($cat->name), 0, 6);
            $datasets[$cat->id] = [
                'categoryId' => $cat->id,
                'label' => strtoupper($cat->name),
                'data' => array_fill(0, count($labels), 0),
                'borderColor' => $color,
                'backgroundColor' => $color . '20',
                'fill' => false,
                'tension' => 0.3
            ];
        }

        [$startDate, $endDate] = $this->getDateRange($year, $period, $monthIndex);

        $chartDataDb = $this->repo->getChartData($startDate, $endDate, $period);

        foreach ($chartDataDb as $row) {
            if (isset($datasets[$row->category_id])) {
                $idx = $row->time_unit - 1;
                $datasets[$row->category_id]['data'][$idx] = (float) $row->total_revenue;
            }
        }
        return ['labels' => $labels, 'datasets' => array_values($datasets), 'period' => $period, 'title' => $title];
    }

    public function getLatestTransactions($year, $period, $monthIndex, $excludeCategories = [])
    {
        [$startDate, $endDate] = $this->getDateRange($year, $period, $monthIndex);
        return $this->repo->getLatestTransactions($startDate, $endDate, $excludeCategories);
    }

    public function getTopProducts($year, $period, $monthIndex, $excludeCategories = [])
    {
        [$startDate, $endDate] = $this->getDateRange($year, $period, $monthIndex);
        return $this->repo->getTopProducts($startDate, $endDate, $excludeCategories);
    }

    public function getTransactionDetailData($id)
    {
        $transaction = $this->repo->getTransactionById($id);
        if (!$transaction) return null;
        return ['transaction' => $transaction, 'items' => $this->repo->getTransactionDetails($id)];
    }

    public function getCategoryProportions($year, $period, $monthIndex, $excludeCategories = [])
    {
        [$startDate, $endDate] = $this->getDateRange($year, $period, $monthIndex);
        return $this->repo->getCategoryProportions($startDate, $endDate, $excludeCategories);
    }

    public function getDailyRevenue($year, $period, $monthIndex)
    {
        [$startDate, $endDate] = $this->getDateRange($year, $period, $monthIndex);
        return $this->repo->getDailyRevenue($startDate, $endDate);
    }

    public function getPeakHours($year, $period, $monthIndex)
    {
        [$startDate, $endDate] = $this->getDateRange($year, $period, $monthIndex);
        return $this->repo->getPeakHours($startDate, $endDate);
    }

    public function getStackedCategoryTrend($year, $period, $monthIndex)
    {
        [$startDate, $endDate] = $this->getDateRange($year, $period, $monthIndex);
        return $this->repo->getStackedCategoryTrend($startDate, $endDate, $period);
    }

    public function getMarketBasket($year, $period, $monthIndex)
    {
        [$startDate, $endDate] = $this->getDateRange($year, $period, $monthIndex);
        return $this->repo->getMarketBasket($startDate, $endDate);
    }

    public function getPeakHourDetail($year, $period, $monthIndex, $dayName, $hour)
    {
        [$startDate, $endDate] = $this->getDateRange($year, $period, $monthIndex);
        return $this->repo->getPeakHourDrillDown($startDate, $endDate, $dayName, $hour);
    }
}
