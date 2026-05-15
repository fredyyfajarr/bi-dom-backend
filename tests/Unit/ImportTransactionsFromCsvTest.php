<?php

namespace Tests\Unit;

use App\Application\Imports\ImportTransactionsFromCsv;
use App\Domain\Contracts\TransactionImportRepositoryInterface;
use PHPUnit\Framework\TestCase;

class ImportTransactionsFromCsvTest extends TestCase
{
    public function test_it_imports_simple_transaction_csv(): void
    {
        $repository = new FakeTransactionImportRepository();
        $useCase = new ImportTransactionsFromCsv($repository);

        $result = $useCase->execute($this->csvFile([
            ['receipt_no', 'trx_date', 'total_amount'],
            ['INV-001', '2026-04-28 10:30:00', '40000'],
        ]));

        $this->assertSame('simple', $result->format);
        $this->assertSame(1, $result->transactionCount);
        $this->assertSame('INV-001', $repository->simpleTransactions[0]['receipt_no']);
    }

    public function test_it_groups_itemized_rows_by_receipt_number(): void
    {
        $repository = new FakeTransactionImportRepository();
        $useCase = new ImportTransactionsFromCsv($repository);

        $result = $useCase->execute($this->csvFile([
            ['receipt_no', 'trx_date', 'product_name', 'qty', 'subtotal'],
            ['INV-001', '2026-04-28 10:30:00', 'Aren Latte', '2', '40000'],
            ['INV-001', '2026-04-28 10:30:00', 'Mix Platter', '1', '35000'],
        ]));

        $this->assertSame('itemized', $result->format);
        $this->assertSame(1, $result->transactionCount);
        $this->assertSame(2, $result->detailCount);
        $this->assertCount(2, $repository->itemizedTransactions[0]['items']);
    }

    private function csvFile(array $rows): string
    {
        $path = tempnam(sys_get_temp_dir(), 'csv-import-');
        $handle = fopen($path, 'w');

        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }

        fclose($handle);

        return $path;
    }
}

class FakeTransactionImportRepository implements TransactionImportRepositoryInterface
{
    public array $simpleTransactions = [];

    public array $itemizedTransactions = [];

    public function saveSimpleTransactions(array $transactions): int
    {
        $this->simpleTransactions = $transactions;

        return count($transactions);
    }

    public function saveItemizedTransactions(array $transactions): int
    {
        $this->itemizedTransactions = $transactions;

        return count($transactions);
    }
}
