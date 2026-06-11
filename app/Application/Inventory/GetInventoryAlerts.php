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
        $startDate = Carbon::now()->subDays(30);
        $totalTransactions = $this->dashboardRepository->getTotalTransactionsSince($startDate);
        $forecastTransactions = $this->calculator->nextWeekTransactions($totalTransactions);
        $usageByInventory = $this->dashboardRepository
            ->getInventoryUsageSince($startDate)
            ->pluck('total_usage', 'inventory_id');
        $hasRecipeUsageHistory = $usageByInventory->isNotEmpty();

        $alerts = $this->inventoryRepository
            ->getAllItems()
            ->map(function ($item) use ($forecastTransactions, $hasRecipeUsageHistory, $usageByInventory) {
                $forecastUsage = $hasRecipeUsageHistory
                    ? $this->calculator->nextWeekUsage((float) ($usageByInventory[$item->id] ?? 0))
                    : null;

                return $this->calculator->buildAlert($item, $forecastTransactions, $forecastUsage);
            });

        return [
            'forecast_next_week_trx' => $forecastTransactions,
            'inventory_alerts' => $alerts,
        ];
    }
}
