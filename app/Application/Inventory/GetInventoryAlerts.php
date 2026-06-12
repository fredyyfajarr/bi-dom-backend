<?php

namespace App\Application\Inventory;

use App\Domain\Inventory\InventoryForecastCalculator;
use App\Repositories\DashboardRepository;
use App\Repositories\InventoryRepository;
use Carbon\Carbon;

class GetInventoryAlerts
{
    public function __construct(
        private readonly InventoryRepository $inventoryRepository,
        private readonly DashboardRepository $dashboardRepository,
        private readonly InventoryForecastCalculator $calculator,
    ) {
    }

    public function execute(): array
    {
        $endDate = $this->dashboardRepository->getLatestTransactionDate() ?? Carbon::now();
        $startDate = $endDate->copy()->subDays(30);
        $totalTransactions = $this->dashboardRepository->getTotalTransactionsBetween($startDate, $endDate);
        $forecastTransactions = $this->calculator->nextWeekTransactions($totalTransactions);
        $usageByInventory = $this->dashboardRepository
            ->getInventoryUsageBetween($startDate, $endDate)
            ->pluck('total_usage', 'inventory_id');
        $hasRecipeUsageHistory = $usageByInventory->isNotEmpty();

        $alerts = $this->inventoryRepository
            ->getAllItems()
            ->map(function ($item) use ($forecastTransactions, $hasRecipeUsageHistory, $usageByInventory) {
                $totalUsage = (float) ($usageByInventory[$item->id] ?? 0);
                $forecastUsage = $hasRecipeUsageHistory
                    ? $this->calculator->nextWeekUsage($totalUsage)
                    : null;
                $hasUsageHistory = $hasRecipeUsageHistory ? $totalUsage > 0 : null;

                return $this->calculator->buildAlert($item, $forecastTransactions, $forecastUsage, $hasUsageHistory);
            });

        return [
            'forecast_next_week_trx' => $forecastTransactions,
            'forecast_window_start' => $startDate->toDateString(),
            'forecast_window_end' => $endDate->toDateString(),
            'inventory_alerts' => $alerts,
        ];
    }
}
