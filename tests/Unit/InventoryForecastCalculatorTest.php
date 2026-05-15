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
    }
}
