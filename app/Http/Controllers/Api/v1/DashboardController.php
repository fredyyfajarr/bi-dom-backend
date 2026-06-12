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
        [$year, $period, $monthIndex, $exclude, $startDate, $endDate, $categoryId] = $request->filters();

        return response()->json(['success' => true, 'data' => $this->service->getKpiStats($year, $period, $monthIndex, $exclude, $startDate, $endDate, $categoryId)]);
    }

    public function getCharts(DashboardFilterRequest $request): JsonResponse
    {
        // Chart sengaja tidak menerima $exclude agar garisnya tetap utuh dan animasinya mulus
        [$year, $period, $monthIndex, , $startDate, $endDate, $categoryId] = $request->filters();

        return response()->json(['success' => true, 'data' => $this->service->getSalesChart($year, $period, $monthIndex, $startDate, $endDate, $categoryId)]);
    }

    public function getChartTransactions(DashboardFilterRequest $request): JsonResponse
    {
        [$year, $period, $monthIndex, , $startDate, $endDate, $categoryId] = $request->filters();
        $pointIndex = (int) $request->query('pointIndex', 0);
        $clickedCategoryId = $request->query('clickedCategoryId');

        return response()->json([
            'success' => true,
            'data' => $this->service->getChartPointTransactions(
                $year,
                $period,
                $monthIndex,
                $pointIndex,
                $startDate,
                $endDate,
                $clickedCategoryId ? (int) $clickedCategoryId : $categoryId,
            ),
        ]);
    }

    public function getTransactions(DashboardFilterRequest $request): JsonResponse
    {
        [$year, $period, $monthIndex, $exclude, $startDate, $endDate, $categoryId] = $request->filters();

        return response()->json(['success' => true, 'data' => $this->service->getLatestTransactions($year, $period, $monthIndex, $exclude, $startDate, $endDate, $categoryId)]);
    }

    public function getTopProducts(DashboardFilterRequest $request): JsonResponse
    {
        [$year, $period, $monthIndex, $exclude, $startDate, $endDate, $categoryId] = $request->filters();

        return response()->json(['success' => true, 'data' => $this->service->getTopProducts($year, $period, $monthIndex, $exclude, $startDate, $endDate, $categoryId)]);
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
        [$year, $period, $monthIndex, $exclude, $startDate, $endDate, $categoryId] = $request->filters();

        return response()->json([
            'success' => true,
            'data' => $this->service->getCategoryProportions($year, $period, $monthIndex, $exclude, $startDate, $endDate, $categoryId),
        ]);
    }

    public function getAdvancedAnalytics(DashboardFilterRequest $request): JsonResponse
    {
        [$year, $period, $monthIndex, , $startDate, $endDate, $categoryId] = $request->filters();

        return response()->json([
            'success' => true,
            'data' => [
                'daily_revenue' => $this->service->getDailyRevenue($year, $period, $monthIndex, $startDate, $endDate, $categoryId),
                'peak_hours' => $this->service->getPeakHours($year, $period, $monthIndex, $startDate, $endDate, $categoryId),
                'stacked_trend' => $this->service->getStackedCategoryTrend($year, $period, $monthIndex, $startDate, $endDate, $categoryId),
                'market_basket' => $this->service->getMarketBasket($year, $period, $monthIndex, $startDate, $endDate, $categoryId),
            ],
        ]);
    }

    public function getPeakHourDetail(DashboardFilterRequest $request): JsonResponse
    {
        [$year, $period, $monthIndex, , $startDate, $endDate, $categoryId] = $request->filters();
        $dayName = $request->query('day');
        $hour = $request->query('hour');

        return response()->json([
            'success' => true,
            'data' => $this->service->getPeakHourDetail($year, $period, $monthIndex, $dayName, $hour, $startDate, $endDate, $categoryId),
        ]);
    }
}
