<?php

namespace Tests\Unit;

use App\Domain\Inventory\InventoryForecastCalculator;
use PHPUnit\Framework\TestCase;

class InventoryForecastCalculatorTest extends TestCase
{
    public function test_it_calculates_next_week_transaction_forecast(): void
    {
        $calculator = new InventoryForecastCalculator();

        $this->assertSame(7, $calculator->nextWeekTransactions(30));
        $this->assertSame(0, $calculator->nextWeekTransactions(0));
    }

    public function test_it_calculates_next_week_inventory_usage_forecast(): void
    {
        $calculator = new InventoryForecastCalculator();

        $this->assertSame(7.0, $calculator->nextWeekUsage(30));
        $this->assertSame(0.0, $calculator->nextWeekUsage(0));
    }

    public function test_it_marks_inventory_as_critical_when_forecast_crosses_min_stock(): void
    {
        $calculator = new InventoryForecastCalculator();

        $alert = $calculator->buildAlert((object) [
            'id' => 1,
            'item_name' => 'Milk',
            'current_stock' => 10,
            'unit' => 'L',
            'usage_per_trx' => 2,
            'min_stock' => 3,
        ], 4);

        $this->assertSame('Kritis', $alert['status']);
        $this->assertSame(8.0, $alert['predicted_usage']);
        $this->assertSame('TRX_AVG_FALLBACK', $alert['usage_basis']);
    }

    public function test_it_prefers_recipe_usage_forecast_when_available(): void
    {
        $calculator = new InventoryForecastCalculator();

        $alert = $calculator->buildAlert((object) [
            'id' => 1,
            'item_name' => 'Milk',
            'current_stock' => 10,
            'unit' => 'L',
            'usage_per_trx' => 2,
            'min_stock' => 3,
        ], 4, 5.5, true);

        $this->assertSame('Aman', $alert['status']);
        $this->assertSame(5.5, $alert['predicted_usage']);
        $this->assertSame('RECIPE_SMA_30D', $alert['usage_basis']);
    }

    public function test_it_marks_zero_recipe_usage_as_no_recent_usage(): void
    {
        $calculator = new InventoryForecastCalculator();

        $alert = $calculator->buildAlert((object) [
            'id' => 1,
            'item_name' => 'Lemon Syrup',
            'current_stock' => 10,
            'unit' => 'L',
            'usage_per_trx' => 2,
            'min_stock' => 3,
        ], 4, 0.0, false);

        $this->assertSame(0.0, $alert['predicted_usage']);
        $this->assertSame('NO_RECENT_USAGE', $alert['usage_basis']);
    }
}
