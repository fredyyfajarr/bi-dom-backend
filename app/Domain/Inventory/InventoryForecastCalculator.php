<?php

namespace App\Domain\Inventory;

class InventoryForecastCalculator
{
    public function nextWeekTransactions(int $totalTransactionsLast30Days): int
    {
        if ($totalTransactionsLast30Days <= 0) {
            return 0;
        }

        return (int) round(($totalTransactionsLast30Days / 30) * 7);
    }

    public function nextWeekUsage(float $totalUsageLast30Days): float
    {
        if ($totalUsageLast30Days <= 0) {
            return 0;
        }

        return ($totalUsageLast30Days / 30) * 7;
    }

    public function buildAlert(
        object $item,
        int $forecastTransactions,
        ?float $forecastUsage = null,
        ?bool $hasUsageHistory = null,
    ): array
    {
        $predictedUsage = $forecastUsage ?? ($forecastTransactions * (float) $item->usage_per_trx);
        $remainingStock = (float) $item->current_stock - $predictedUsage;
        $usageBasis = match (true) {
            $forecastUsage === null => 'TRX_AVG_FALLBACK',
            $hasUsageHistory === true => 'RECIPE_SMA_30D',
            default => 'NO_RECENT_USAGE',
        };

        return [
            'id' => $item->id,
            'item_name' => $item->item_name,
            'current_stock' => $item->current_stock,
            'unit' => $item->unit,
            'predicted_usage' => round($predictedUsage, 2),
            'usage_basis' => $usageBasis,
            'status' => $remainingStock <= (float) $item->min_stock ? 'Kritis' : 'Aman',
        ];
    }
}
