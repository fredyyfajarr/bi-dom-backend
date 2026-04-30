<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $service;

    public function __construct(DashboardService $service)
    {
        $this->service = $service;
    }

    // --- HELPER UNTUK MEMECAH URL PARAMETER ---
    private function getParams(Request $request)
    {
        $excludeRaw = $request->query('exclude', '');

        // array_filter memastikan jika kosong, hasilnya [] bukan [""]
        $excludeCategories = $excludeRaw ? array_filter(explode(',', $excludeRaw)) : [];

        return [
            $request->query('year', date('Y')),
            $request->query('period', 'year'),
            $request->query('monthIndex', null),
            $excludeCategories
        ];
    }

    // --- ENDPOINT METADATA & ALERTS ---
    public function getAvailableYears()
    {
        return response()->json(['success' => true, 'data' => $this->service->getAvailableYears()]);
    }

    public function getCategoriesList()
    {
        return response()->json(['success' => true, 'data' => $this->service->getCategoriesList()]);
    }

    public function getLowStockAlerts()
    {
        return response()->json(['success' => true, 'data' => $this->service->getLowStockProducts()]);
    }

    // --- ENDPOINT DATA DASHBOARD ---
    public function getKpi(Request $request)
    {
        [$year, $period, $monthIndex, $exclude] = $this->getParams($request);
        return response()->json(['success' => true, 'data' => $this->service->getKpiStats($year, $period, $monthIndex, $exclude)]);
    }

    public function getCharts(Request $request)
    {
        // Chart sengaja tidak menerima $exclude agar garisnya tetap utuh dan animasinya mulus
        [$year, $period, $monthIndex] = $this->getParams($request);
        return response()->json(['success' => true, 'data' => $this->service->getSalesChart($year, $period, $monthIndex)]);
    }

    public function getTransactions(Request $request)
    {
        [$year, $period, $monthIndex, $exclude] = $this->getParams($request);
        return response()->json(['success' => true, 'data' => $this->service->getLatestTransactions($year, $period, $monthIndex, $exclude)]);
    }

    public function getTopProducts(Request $request)
    {
        [$year, $period, $monthIndex, $exclude] = $this->getParams($request);
        return response()->json(['success' => true, 'data' => $this->service->getTopProducts($year, $period, $monthIndex, $exclude)]);
    }

    // --- ENDPOINT MODAL DETAIL ---
    public function getTransactionDetail($id)
    {
        $data = $this->service->getTransactionDetailData($id);
        if (!$data) return response()->json(['success' => false, 'message' => 'Transaction not found'], 404);
        return response()->json(['success' => true, 'data' => $data]);
    }

    public function getDonutData(Request $request)
    {
        [$year, $period, $monthIndex, $exclude] = $this->getParams($request);
        return response()->json([
            'success' => true,
            'data' => $this->service->getCategoryProportions($year, $period, $monthIndex, $exclude)
        ]);
    }

    public function getAdvancedAnalytics(Request $request)
    {
        [$year, $period, $monthIndex] = $this->getParams($request);
        return response()->json([
            'success' => true,
            'data' => [
                'daily_revenue' => $this->service->getDailyRevenue($year, $period, $monthIndex),
                'peak_hours' => $this->service->getPeakHours($year, $period, $monthIndex),
                'stacked_trend' => $this->service->getStackedCategoryTrend($year, $period, $monthIndex),
                'market_basket' => $this->service->getMarketBasket($year, $period, $monthIndex)
            ]
        ]);
    }

    public function getPeakHourDetail(Request $request)
    {
        [$year, $period, $monthIndex] = $this->getParams($request);
        $dayName = $request->query('day');
        $hour = $request->query('hour');

        return response()->json([
            'success' => true,
            'data' => $this->service->getPeakHourDetail($year, $period, $monthIndex, $dayName, $hour)
        ]);
    }
}
