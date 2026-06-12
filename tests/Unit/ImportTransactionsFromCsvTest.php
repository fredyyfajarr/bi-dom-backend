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
            ['receipt_no', 'trx_date', 'product_name', 'qty', 'subtotal', 'payment_method'],
            ['INV-001', '2026-04-28 10:30:00', 'Aren Latte', '2', '40000', 'qris'],
            ['INV-001', '2026-04-28 10:30:00', 'Mix Platter', '1', '35000', 'qris'],
        ]));

        $this->assertSame('itemized', $result->format);
        $this->assertSame(1, $result->transactionCount);
        $this->assertSame(2, $result->detailCount);
        $this->assertCount(2, $repository->itemizedTransactions[0]['items']);
        $this->assertSame('QRIS', $repository->itemizedTransactions[0]['payment_method']);
    }

    public function test_it_rejects_only_receipts_with_unknown_products(): void
    {
        $repository = new FakeTransactionImportRepository();
        $useCase = new ImportTransactionsFromCsv($repository);

        $result = $useCase->execute($this->csvFile([
            ['receipt_no', 'trx_date', 'product_name', 'qty', 'subtotal'],
            ['INV-001', '2026-04-28 10:30:00', 'Aren Latte', '2', '40000'],
            ['INV-002', '2026-04-28 10:35:00', 'Typo Latte', '1', '20000'],
            ['INV-003', '2026-04-28 10:40:00', 'Mix Platter', '1', '35000'],
        ]));

        $this->assertSame(2, $result->transactionCount);
        $this->assertSame(2, $result->detailCount);
        $this->assertSame(1, $result->rejectedCount);
        $this->assertSame('INV-002', $result->rejectedReceipts[0]['receipt_no']);
        $this->assertSame(['Typo Latte'], $result->rejectedReceipts[0]['products']);
        $this->assertSame(['INV-001', 'INV-003'], array_column($repository->itemizedTransactions, 'receipt_no'));
    }

    public function test_it_accepts_csv_headers_with_utf8_bom(): void
    {
        $repository = new FakeTransactionImportRepository();
        $useCase = new ImportTransactionsFromCsv($repository);

        $result = $useCase->execute($this->csvFile([
            ["\xEF\xBB\xBFreceipt_no", 'trx_date', 'product_name', 'qty', 'subtotal'],
            ['INV-001', '2026-04-28 10:30:00', 'Aren Latte', '2', '40000'],
        ]));

        $this->assertSame(1, $result->transactionCount);
        $this->assertSame('INV-001', $repository->itemizedTransactions[0]['receipt_no']);
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
    private array $existingProductNames = ['Aren Latte', 'Mix Platter'];

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

    public function getExistingReceiptNos(array $receiptNos): array
    {
        return [];
    }

    public function getExistingProductNames(array $productNames): array
    {
        return array_values(array_intersect($productNames, $this->existingProductNames));
    }
}
