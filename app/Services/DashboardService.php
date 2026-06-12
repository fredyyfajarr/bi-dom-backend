<?php

namespace App\Services;

use App\Repositories\DashboardRepository;
use Carbon\Carbon;

class DashboardService
{
    public function __construct(private readonly DashboardRepository $repo) {}

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function getDateRange(string|int $year, string $period, mixed $monthIndex, ?string $startDate = null, ?string $endDate = null): array
    {
        if ($startDate && $endDate) {
            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();
        } elseif ($period === 'month' && $monthIndex !== null) {
            $month = $monthIndex + 1;
            $start = Carbon::create($year, $month, 1)->startOfMonth();
            $end = Carbon::create($year, $month, 1)->endOfMonth();
        } else {
            $start = Carbon::create($year, 1, 1)->startOfYear();
            $end = Carbon::create($year, 1, 1)->endOfYear();
        }

        return [$start, $end];
    }

    /**
     * @return array<int, int>
     */
    public function getAvailableYears(): array
    {
        $years = $this->repo->getAvailableYears();

        return empty($years) ? [(int) date('Y')] : $years;
    }

    public function getCategoriesList(): mixed
    {
        return $this->repo->getCategoriesList();
    }

    /**
     * @return array<int, array{name: string, stock: mixed, unit: string}>
     */
    public function getLowStockProducts(): array
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
                    'name' => $item->item_name,
                    'stock' => $item->current_stock,
                    'unit' => $item->unit,
                ];
            }
        }

        return collect($criticalItems)->sortBy('stock')->values()->all();
    }

    public function getKpiStats(string|int $year, string $period, mixed $monthIndex, array $excludeCategories = [], ?string $startDate = null, ?string $endDate = null, ?int $categoryId = null): array
    {
        [$startDate, $endDate] = $this->getDateRange($year, $period, $monthIndex, $startDate, $endDate);

        $data = $this->repo->getKpiStats($startDate, $endDate, $excludeCategories, $categoryId);

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
            'transaction_count' => $trxCount,
        ];
    }

    public function getSalesChart(string|int $year, string $period = 'year', mixed $monthIndex = null, ?string $startDate = null, ?string $endDate = null, ?int $categoryId = null): array
    {
        $labels = [];
        $dbCategories = $this->repo->getCategoriesList();
        $colorPalette = ['#dc2626', '#2563eb', '#16a34a', '#ca8a04', '#7c3aed', '#db2777'];

        if ($period === 'year') {
            $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            $title = "ANNUAL_CATEGORY_ANALYSIS: $year";
        } else {
            $actualMonth = $monthIndex + 1;
            $monthDate = Carbon::create((int) $year, $actualMonth, 1);
            $daysInMonth = $monthDate->daysInMonth;
            $monthName = $monthDate->format('M');
            for ($i = 1; $i <= $daysInMonth; $i++) {
                $labels[] = $i.' '.$monthName;
            }
            $title = 'DAILY_CATEGORY_ANALYSIS: '.$monthDate->format('F Y');
        }

        $datasets = [];
        foreach ($dbCategories as $index => $cat) {
            $color = $colorPalette[$index] ?? '#'.substr(md5($cat->name), 0, 6);
            $datasets[$cat->id] = [
                'categoryId' => $cat->id,
                'label' => strtoupper($cat->name),
                'data' => array_fill(0, count($labels), 0),
                'borderColor' => $color,
                'backgroundColor' => $color.'20',
                'fill' => false,
                'tension' => 0.3,
            ];
        }

        [$rangeStartDate, $rangeEndDate] = $this->getDateRange($year, $period, $monthIndex, $startDate, $endDate);

        $chartDataDb = $this->repo->getChartData($rangeStartDate, $rangeEndDate, $period, $categoryId);

        foreach ($chartDataDb as $row) {
            if (isset($datasets[$row->category_id])) {
                $idx = $row->time_unit - 1;
                $datasets[$row->category_id]['data'][$idx] = (float) $row->total_revenue;
            }
        }

        return ['labels' => $labels, 'datasets' => array_values($datasets), 'period' => $period, 'title' => $title];
    }

    public function getChartPointTransactions(string|int $year, string $period, mixed $monthIndex, int $pointIndex, ?string $startDate = null, ?string $endDate = null, ?int $categoryId = null): array
    {
        if ($period === 'month' && $monthIndex !== null) {
            $month = (int) $monthIndex + 1;
            $pointStart = Carbon::create((int) $year, $month, max(1, $pointIndex + 1))->startOfDay();
            $pointEnd = $pointStart->copy()->endOfDay();
            $label = $pointStart->format('d M Y');
        } else {
            $month = max(1, min(12, $pointIndex + 1));
            $pointStart = Carbon::create((int) $year, $month, 1)->startOfMonth();
            $pointEnd = $pointStart->copy()->endOfMonth();
            $label = $pointStart->format('F Y');
        }

        if ($startDate && $endDate) {
            $range = $this->getDateRange($year, $period, $monthIndex, $startDate, $endDate);
            $pointStart = $pointStart->max($range[0]);
            $pointEnd = $pointEnd->min($range[1]);
        }

        return [
            'label' => $label,
            'transactions' => $this->repo->getTransactionsForDrillThrough($pointStart, $pointEnd, $categoryId),
        ];
    }

    public function getLatestTransactions(string|int $year, string $period, mixed $monthIndex, array $excludeCategories = [], ?string $startDate = null, ?string $endDate = null, ?int $categoryId = null): mixed
    {
        [$startDate, $endDate] = $this->getDateRange($year, $period, $monthIndex, $startDate, $endDate);

        return $this->repo->getLatestTransactions($startDate, $endDate, $excludeCategories, $categoryId);
    }

    public function getTopProducts(string|int $year, string $period, mixed $monthIndex, array $excludeCategories = [], ?string $startDate = null, ?string $endDate = null, ?int $categoryId = null): mixed
    {
        [$startDate, $endDate] = $this->getDateRange($year, $period, $monthIndex, $startDate, $endDate);

        return $this->repo->getTopProducts($startDate, $endDate, $excludeCategories, $categoryId);
    }

    public function getTransactionDetailData(int $id): ?array
    {
        $transaction = $this->repo->getTransactionById($id);
        if (! $transaction) {
            return null;
        }

        return ['transaction' => $transaction, 'items' => $this->repo->getTransactionDetails($id)];
    }

    public function getCategoryProportions(string|int $year, string $period, mixed $monthIndex, array $excludeCategories = [], ?string $startDate = null, ?string $endDate = null, ?int $categoryId = null): mixed
    {
        [$startDate, $endDate] = $this->getDateRange($year, $period, $monthIndex, $startDate, $endDate);

        return $this->repo->getCategoryProportions($startDate, $endDate, $excludeCategories, $categoryId);
    }

    public function getDailyRevenue(string|int $year, string $period, mixed $monthIndex, ?string $startDate = null, ?string $endDate = null, ?int $categoryId = null): mixed
    {
        [$startDate, $endDate] = $this->getDateRange($year, $period, $monthIndex, $startDate, $endDate);

        return $this->repo->getDailyRevenue($startDate, $endDate, $categoryId);
    }

    public function getPeakHours(string|int $year, string $period, mixed $monthIndex, ?string $startDate = null, ?string $endDate = null, ?int $categoryId = null): mixed
    {
        [$startDate, $endDate] = $this->getDateRange($year, $period, $monthIndex, $startDate, $endDate);

        return $this->repo->getPeakHours($startDate, $endDate, $categoryId);
    }

    public function getStackedCategoryTrend(string|int $year, string $period, mixed $monthIndex, ?string $startDate = null, ?string $endDate = null, ?int $categoryId = null): mixed
    {
        [$startDate, $endDate] = $this->getDateRange($year, $period, $monthIndex, $startDate, $endDate);

        return $this->repo->getStackedCategoryTrend($startDate, $endDate, $period, $categoryId);
    }

    public function getMarketBasket(string|int $year, string $period, mixed $monthIndex, ?string $startDate = null, ?string $endDate = null, ?int $categoryId = null): mixed
    {
        [$startDate, $endDate] = $this->getDateRange($year, $period, $monthIndex, $startDate, $endDate);

        return $this->repo->getMarketBasket($startDate, $endDate, $categoryId);
    }

    public function getPeakHourDetail(string|int $year, string $period, mixed $monthIndex, mixed $dayName, mixed $hour, ?string $startDate = null, ?string $endDate = null, ?int $categoryId = null): mixed
    {
        [$startDate, $endDate] = $this->getDateRange($year, $period, $monthIndex, $startDate, $endDate);

        return $this->repo->getPeakHourDrillDown($startDate, $endDate, $dayName, $hour, $categoryId);
    }
}
