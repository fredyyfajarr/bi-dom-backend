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

    public function buildAlert(object $item, int $forecastTransactions): array
    {
        $predictedUsage = $forecastTransactions * (float) $item->usage_per_trx;
        $remainingStock = (float) $item->current_stock - $predictedUsage;

        return [
            'id' => $item->id,
            'item_name' => $item->item_name,
            'current_stock' => $item->current_stock,
            'unit' => $item->unit,
            'predicted_usage' => round($predictedUsage, 2),
            'status' => $remainingStock <= (float) $item->min_stock ? 'Kritis' : 'Aman',
        ];
    }
}
