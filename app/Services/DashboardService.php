<?php

namespace App\Services;

use App\Repositories\DashboardRepository;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardService
{
    protected $repo;

    // ========================================================================
    // KONFIGURASI KOLOM: Pastikan nama kolom sesuai dengan database Anda
    // ========================================================================
    protected $revenueCol = 'transaction_details.subtotal'; // Atau (price * qty)
    protected $qtyCol = 'transaction_details.qty';          // Atau quantity
    // ========================================================================

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
        $years = DB::table('transactions')->selectRaw('YEAR(created_at) as year')->distinct()->orderBy('year', 'desc')->pluck('year')->toArray();
        return empty($years) ? [(int) date('Y')] : $years;
    }

    public function getCategoriesList()
    {
        return DB::table('categories')->select('id', 'name')->get();
    }

    public function getLowStockProducts()
    {
        // 1. Ambil data total transaksi 30 hari ke belakang (Sama dengan InventoryService)
        $startDate = Carbon::now()->subDays(30);
        $totalTrx30Days = DB::table('transactions')
            ->where('created_at', '>=', $startDate) // Ganti jadi trx_date jika di database Anda memakai trx_date
            ->count('id');

        // 2. Hitung Rata-rata Harian & Forecast 1 Minggu
        $dailyAverage = $totalTrx30Days > 0 ? ($totalTrx30Days / 30) : 0;
        $totalForecastTrx = round($dailyAverage * 7);

        // 3. Ambil data bahan baku dari tabel inventories
        // Pastikan nama tabel Anda benar (inventories)
        $inventories = DB::table('inventories')->get();

        $criticalItems = [];

        // 4. Lakukan pengecekan SMA untuk setiap bahan baku
        foreach ($inventories as $item) {
            $predictedUsage = $totalForecastTrx * $item->usage_per_trx;
            $sisaStok = $item->current_stock - $predictedUsage;

            // Jika statusnya KRITIS (sisa stok <= min_stock), masukkan ke alert Dashboard
            if ($sisaStok <= $item->min_stock) {
                $criticalItems[] = [
                    'name'  => $item->item_name,     // Di-mapping ke 'name' agar Frontend React tidak error
                    'stock' => $item->current_stock, // Di-mapping ke 'stock'
                    'unit'  => $item->unit
                ];
            }
        }

        // Urutkan dari stok yang paling tipis ke paling banyak
        return collect($criticalItems)->sortBy('stock')->values()->all();
    }
   // --- KPI STATS ---
    public function getKpiStats($year, $period, $monthIndex, $excludeCategories = [])
    {
        [$startDate, $endDate] = $this->getDateRange($year, $period, $monthIndex);

        if (empty($excludeCategories)) {
            // OPTIMASI: Tanpa filter kategori, ambil LANGSUNG dari tabel transactions (sangat cepat)
            $data = DB::table('transactions')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw('
                    SUM(total_amount) as total_revenue,
                    SUM(total_cogs) as total_cogs,
                    SUM(net_profit) as net_profit,
                    COUNT(id) as total_count
                ')->first();

            $revenue = (float) ($data->total_revenue ?? 0);
            $cogs = (float) ($data->total_cogs ?? 0);
            $netProfit = (float) ($data->net_profit ?? 0);
            $trxCount = (int) ($data->total_count ?? 0);

        } else {
            // JIKA ADA FILTER: Ambil dari detail transaksi menggunakan kolom yang sudah fix
            $query = DB::table('transaction_details')
                ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
                ->join('products', 'transaction_details.product_id', '=', 'products.id')
                ->whereBetween('transactions.created_at', [$startDate, $endDate])
                ->whereNotIn('products.category_id', $excludeCategories);

            $data = $query->select(
                DB::raw('SUM(transaction_details.subtotal) as total_revenue'),
                DB::raw('SUM(transaction_details.subtotal_cogs) as total_cogs'),
                DB::raw('COUNT(DISTINCT transactions.id) as total_count')
            )->first();

            $revenue = (float) ($data->total_revenue ?? 0);
            $cogs = (float) ($data->total_cogs ?? 0);
            $netProfit = $revenue - $cogs;
            $trxCount = (int) ($data->total_count ?? 0);
        }

        // Kalkulasi Margin
        $profitMargin = $revenue > 0 ? ($netProfit / $revenue) * 100 : 0;

        return [
            'revenue' => $revenue,
            'total_cogs' => $cogs,
            'net_profit' => $netProfit,
            'profit_margin' => round($profitMargin, 1),
            'transaction_count' => $trxCount
        ];
    }

    // --- SALES CHART ---
    public function getSalesChart($year, $period = 'year', $monthIndex = null)
    {
        $labels = [];
        $dbCategories = DB::table('categories')->select('id', 'name')->get();
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

        $chartDataDb = DB::table('transaction_details')
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->join('products', 'transaction_details.product_id', '=', 'products.id')
            ->whereBetween('transactions.created_at', [$startDate, $endDate])
            ->select(
                'products.category_id',
                DB::raw("SUM({$this->revenueCol}) as total_revenue"),
                DB::raw($period === 'year' ? 'MONTH(transactions.created_at) as time_unit' : 'DAY(transactions.created_at) as time_unit')
            )
            ->groupBy('products.category_id', 'time_unit')->get();

        foreach ($chartDataDb as $row) {
            if (isset($datasets[$row->category_id])) {
                $idx = $row->time_unit - 1;
                $datasets[$row->category_id]['data'][$idx] = (float) $row->total_revenue;
            }
        }
        return ['labels' => $labels, 'datasets' => array_values($datasets), 'period' => $period, 'title' => $title];
    }

    // --- LATEST TRANSACTIONS ---
    public function getLatestTransactions($year, $period, $monthIndex, $excludeCategories = [])
    {
        [$startDate, $endDate] = $this->getDateRange($year, $period, $monthIndex);

        $query = DB::table('transaction_details')
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->join('products', 'transaction_details.product_id', '=', 'products.id')
            ->whereBetween('transactions.created_at', [$startDate, $endDate]);

        if (!empty($excludeCategories)) {
            $query->whereNotIn('products.category_id', $excludeCategories);
        }

        return $query->select(
                'transactions.id',
                'transactions.receipt_no',
                DB::raw("SUM({$this->revenueCol}) as total_amount")
            )
            ->groupBy('transactions.id', 'transactions.receipt_no')
            ->orderByRaw('MAX(transactions.created_at) DESC')
            ->limit(10)->get();
    }

    // --- TOP PRODUCTS ---
    public function getTopProducts($year, $period, $monthIndex, $excludeCategories = [])
    {
        [$startDate, $endDate] = $this->getDateRange($year, $period, $monthIndex);

        $query = DB::table('transaction_details')
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->join('products', 'transaction_details.product_id', '=', 'products.id')
            ->whereBetween('transactions.created_at', [$startDate, $endDate]);

        if (!empty($excludeCategories)) {
            $query->whereNotIn('products.category_id', $excludeCategories);
        }

        return $query->select(
                'products.name',
                DB::raw("SUM({$this->qtyCol}) as total_qty"),
                DB::raw("SUM({$this->revenueCol}) as total_revenue")
            )
            ->groupBy('products.id', 'products.name')
            ->orderBy('total_qty', 'desc')
            ->limit(5)->get();
    }

    public function getTransactionDetailData($id)
    {
        $transaction = $this->repo->getTransactionById($id);
        if (!$transaction) return null;
        return ['transaction' => $transaction, 'items' => $this->repo->getTransactionDetails($id)];
    }


    // Tambahkan di dalam class DashboardService
public function getCategoryProportions($year, $period, $monthIndex, $excludeCategories = [])
    {
        [$startDate, $endDate] = $this->getDateRange($year, $period, $monthIndex);

        $query = DB::table('transaction_details')
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->join('products', 'transaction_details.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->whereBetween('transactions.created_at', [$startDate, $endDate]);

        if (!empty($excludeCategories)) {
            $query->whereNotIn('products.category_id', $excludeCategories);
        }

        return $query->select(
                'categories.name as label',
                DB::raw("SUM({$this->qtyCol}) as value")
            )
            ->groupBy('categories.id', 'categories.name')
            ->get();
    }

    // --- 1. BAR CHART: PENDAPATAN PER HARI ---
    public function getDailyRevenue($year, $period, $monthIndex)
    {
        [$startDate, $endDate] = $this->getDateRange($year, $period, $monthIndex);
        return DB::table('transactions')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DAYNAME(created_at) as day_name, DAYOFWEEK(created_at) as day_num, SUM(total_amount) as total')
            ->groupBy('day_name', 'day_num')
            ->orderBy('day_num')
            ->get();
    }

    // --- 2. HEATMAP: JAM SIBUK (PEAK HOURS) ---
    public function getPeakHours($year, $period, $monthIndex)
    {
        [$startDate, $endDate] = $this->getDateRange($year, $period, $monthIndex);
        return DB::table('transactions')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DAYNAME(created_at) as day_name, HOUR(created_at) as hour, COUNT(id) as total_trx')
            ->groupBy('day_name', 'hour')
            ->get();
    }

    // --- 3. STACKED BAR: TREN KATEGORI HARIAN/BULANAN ---
    public function getStackedCategoryTrend($year, $period, $monthIndex)
    {
        [$startDate, $endDate] = $this->getDateRange($year, $period, $monthIndex);
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

    // --- 4. MARKET BASKET ANALYSIS (FREQUENTLY BOUGHT TOGETHER) ---
    public function getMarketBasket($year, $period, $monthIndex)
    {
        [$startDate, $endDate] = $this->getDateRange($year, $period, $monthIndex);

        // Trik SQL: Join tabel transaction_details dengan dirinya sendiri (Self-Join)
        // td1.product_id < td2.product_id digunakan agar kombinasi A-B dan B-A tidak dihitung dua kali
        return DB::table('transaction_details as td1')
            ->join('transaction_details as td2', function ($join) {
                $join->on('td1.transaction_id', '=', 'td2.transaction_id')
                     ->whereRaw('td1.product_id < td2.product_id');
            })
            ->join('products as p1', 'td1.product_id', '=', 'p1.id')
            ->join('products as p2', 'td2.product_id', '=', 'p2.id')
            ->join('transactions as trx', 'td1.transaction_id', '=', 'trx.id')
            ->whereBetween('trx.created_at', [$startDate, $endDate])
            ->select(
                'p1.name as product_a',
                'p2.name as product_b',
                DB::raw('COUNT(DISTINCT td1.transaction_id) as times_bought_together')
            )
            ->groupBy('product_a', 'product_b')
            ->orderByDesc('times_bought_together')
            ->limit(5) // Ambil 5 pasangan paling populer
            ->get();
    }




}
