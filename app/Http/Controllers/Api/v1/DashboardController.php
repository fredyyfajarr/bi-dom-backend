<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\DashboardFilterRequest;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardService $service) {}

    public function getAvailableYears(): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $this->service->getAvailableYears()]);
    }

    public function getCategoriesList(): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $this->service->getCategoriesList()]);
    }

    public function getLowStockAlerts(): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $this->service->getLowStockProducts()]);
    }

    public function getKpi(DashboardFilterRequest $request): JsonResponse
    {
        [$year, $period, $monthIndex, $exclude] = $request->filters();

        return response()->json(['success' => true, 'data' => $this->service->getKpiStats($year, $period, $monthIndex, $exclude)]);
    }

    public function getCharts(DashboardFilterRequest $request): JsonResponse
    {
        // Chart sengaja tidak menerima $exclude agar garisnya tetap utuh dan animasinya mulus
        [$year, $period, $monthIndex] = $request->filters();

        return response()->json(['success' => true, 'data' => $this->service->getSalesChart($year, $period, $monthIndex)]);
    }

    public function getTransactions(DashboardFilterRequest $request): JsonResponse
    {
        [$year, $period, $monthIndex, $exclude] = $request->filters();

        return response()->json(['success' => true, 'data' => $this->service->getLatestTransactions($year, $period, $monthIndex, $exclude)]);
    }

    public function getTopProducts(DashboardFilterRequest $request): JsonResponse
    {
        [$year, $period, $monthIndex, $exclude] = $request->filters();

        return response()->json(['success' => true, 'data' => $this->service->getTopProducts($year, $period, $monthIndex, $exclude)]);
    }

    public function getTransactionDetail(int $id): JsonResponse
    {
        $data = $this->service->getTransactionDetailData($id);
        if (! $data) {
            return response()->json(['success' => false, 'message' => 'Transaction not found'], 404);
        }

        return response()->json(['success' => true, 'data' => $data]);
    }

    public function getDonutData(DashboardFilterRequest $request): JsonResponse
    {
        [$year, $period, $monthIndex, $exclude] = $request->filters();

        return response()->json([
            'success' => true,
            'data' => $this->service->getCategoryProportions($year, $period, $monthIndex, $exclude),
        ]);
    }

    public function getAdvancedAnalytics(DashboardFilterRequest $request): JsonResponse
    {
        [$year, $period, $monthIndex] = $request->filters();

        return response()->json([
            'success' => true,
            'data' => [
                'daily_revenue' => $this->service->getDailyRevenue($year, $period, $monthIndex),
                'peak_hours' => $this->service->getPeakHours($year, $period, $monthIndex),
                'stacked_trend' => $this->service->getStackedCategoryTrend($year, $period, $monthIndex),
                'market_basket' => $this->service->getMarketBasket($year, $period, $monthIndex),
            ],
        ]);
    }

    public function getPeakHourDetail(DashboardFilterRequest $request): JsonResponse
    {
        [$year, $period, $monthIndex] = $request->filters();
        $dayName = $request->query('day');
        $hour = $request->query('hour');

        return response()->json([
            'success' => true,
            'data' => $this->service->getPeakHourDetail($year, $period, $monthIndex, $dayName, $hour),
        ]);
    }
}
