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
        $totalTransactions = $this->dashboardRepository->getTotalTransactionsSince(Carbon::now()->subDays(30));
        $forecastTransactions = $this->calculator->nextWeekTransactions($totalTransactions);

        $alerts = $this->inventoryRepository
            ->getAllItems()
            ->map(fn ($item) => $this->calculator->buildAlert($item, $forecastTransactions));

        return [
            'forecast_next_week_trx' => $forecastTransactions,
            'inventory_alerts' => $alerts,
        ];
    }
}
