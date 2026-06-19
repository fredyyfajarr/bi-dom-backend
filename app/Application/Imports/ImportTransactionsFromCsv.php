<?php

namespace App\Application\Imports;

use App\Domain\Contracts\TransactionImportRepositoryInterface;
use Exception;

class ImportTransactionsFromCsv
{
    private const SIMPLE_COLUMNS = ['receipt_no', 'trx_date', 'total_amount'];

    private const ITEMIZED_COLUMNS = ['receipt_no', 'trx_date', 'product_name', 'qty', 'subtotal'];

    public function __construct(
        private readonly TransactionImportRepositoryInterface $repository,
    ) {
    }

    public function execute(string $filePath): CsvTransactionImportResult
    {
        $rows = $this->readCsvRows($filePath);
        if (empty($rows)) {
            throw new Exception('File CSV kosong atau format tidak sesuai.');
        }

        $header = $this->normalizeHeader(array_shift($rows));
        if ($this->hasColumns($header, self::ITEMIZED_COLUMNS)) {
            return $this->importItemizedRows($header, $rows);
        }

        if ($this->hasColumns($header, self::SIMPLE_COLUMNS)) {
            return $this->importSimpleRows($header, $rows);
        }

        throw new Exception('Header CSV tidak dikenali. Gunakan format receipt_no,trx_date,total_amount atau receipt_no,trx_date,product_name,qty,subtotal. Kolom payment_method boleh ditambahkan dan opsional.');
    }

    private function importSimpleRows(array $header, array $rows): CsvTransactionImportResult
    {
        $transactions = [];
        $rejectedReceipts = [];
        foreach ($rows as $row) {
            $record = $this->combineRow($header, $row);
            $receiptNo = trim((string) ($record['receipt_no'] ?? ''));

            if (!$this->isFilled($record, self::SIMPLE_COLUMNS)) {
                if ($receiptNo !== '') {
                    $rejectedReceipts[$receiptNo] = [
                        'receipt_no' => $receiptNo,
                        'reason' => 'Data tidak lengkap (kolom wajib ada yang kosong)',
                        'products' => [],
                    ];
                }
                continue;
            }

            if (isset($rejectedReceipts[$receiptNo])) {
                continue;
            }

            $transactions[$receiptNo] = [
                'receipt_no' => $receiptNo,
                'trx_date' => trim($record['trx_date']),
                'payment_method' => $this->normalizePaymentMethod($record['payment_method'] ?? null),
                'total_amount' => (float) $record['total_amount'],
            ];
        }

        if (empty($transactions) && empty($rejectedReceipts)) {
            throw new Exception('Tidak ada transaksi valid di CSV.');
        }

        // Filter out duplicate receipts
        $allReceipts = array_column($transactions, 'receipt_no');
        $existingReceipts = $this->repository->getExistingReceiptNos($allReceipts);

        $skippedReceipts = [];
        $filteredTransactions = [];
        foreach ($transactions as $trx) {
            if (in_array($trx['receipt_no'], $existingReceipts, true)) {
                $skippedReceipts[] = $trx['receipt_no'];
            } else {
                $filteredTransactions[] = $trx;
            }
        }

        $insertedCount = 0;
        if (!empty($filteredTransactions)) {
            $insertedCount = $this->repository->saveSimpleTransactions($filteredTransactions);
        }

        $allRejected = array_values($rejectedReceipts);

        return new CsvTransactionImportResult(
            $insertedCount,
            0,
            'simple',
            count($skippedReceipts),
            $skippedReceipts,
            count($allRejected),
            $allRejected,
        );
    }

    private function importItemizedRows(array $header, array $rows): CsvTransactionImportResult
    {
        $transactions = [];
        $rejectedReceipts = [];
        foreach ($rows as $row) {
            $record = $this->combineRow($header, $row);
            $receiptNo = trim((string) ($record['receipt_no'] ?? ''));

            if (!$this->isFilled($record, self::ITEMIZED_COLUMNS)) {
                if ($receiptNo !== '') {
                    $rejectedReceipts[$receiptNo] = [
                        'receipt_no' => $receiptNo,
                        'reason' => 'Data tidak lengkap (kolom wajib ada yang kosong)',
                        'products' => [],
                    ];
                    unset($transactions[$receiptNo]);
                }
                continue;
            }

            if (isset($rejectedReceipts[$receiptNo])) {
                continue;
            }

            if (!isset($transactions[$receiptNo])) {
                $transactions[$receiptNo] = [
                    'receipt_no' => $receiptNo,
                    'trx_date' => trim($record['trx_date']),
                    'payment_method' => $this->normalizePaymentMethod($record['payment_method'] ?? null),
                    'items' => [],
                ];
            }

            $transactions[$receiptNo]['items'][] = [
                'product_name' => trim($record['product_name']),
                'qty' => (int) $record['qty'],
                'subtotal' => (float) $record['subtotal'],
            ];
        }

        if (empty($transactions) && empty($rejectedReceipts)) {
            throw new Exception('Tidak ada detail transaksi valid di CSV.');
        }

        // Filter out duplicate receipts
        $allReceipts = array_keys($transactions);
        $existingReceipts = $this->repository->getExistingReceiptNos($allReceipts);

        $skippedReceipts = [];
        foreach ($existingReceipts as $receipt) {
            if (isset($transactions[$receipt])) {
                $skippedReceipts[] = $receipt;
                unset($transactions[$receipt]);
            }
        }

        $unknownProductsRejected = $this->rejectReceiptsWithUnknownProducts($transactions);
        $allRejected = array_merge(array_values($rejectedReceipts), $unknownProductsRejected);

        $insertedCount = 0;
        $detailCount = 0;
        if (!empty($transactions)) {
            $detailCount = array_sum(array_map(fn ($transaction) => count($transaction['items']), $transactions));
            $insertedCount = $this->repository->saveItemizedTransactions(array_values($transactions));
        }

        return new CsvTransactionImportResult(
            $insertedCount,
            $detailCount,
            'itemized',
            count($skippedReceipts),
            $skippedReceipts,
            count($allRejected),
            $allRejected,
        );
    }

    private function readCsvRows(string $filePath): array
    {
        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            throw new Exception('File CSV tidak dapat dibaca.');
        }

        $rows = [];
        while (($row = fgetcsv($handle, 0, ',')) !== false) {
            if ($row === [null] || $row === false) {
                continue;
            }

            $rows[] = $row;
        }
        fclose($handle);

        return $rows;
    }

    private function normalizeHeader(array $header): array
    {
        return array_map(
            fn ($column) => strtolower(trim((string) $column, "\xEF\xBB\xBF \t\n\r\0\x0B")),
            $header,
        );
    }

    private function hasColumns(array $header, array $columns): bool
    {
        return empty(array_diff($columns, $header));
    }

    private function combineRow(array $header, array $row): array
    {
        $row = array_pad($row, count($header), null);

        return array_combine($header, array_slice($row, 0, count($header))) ?: [];
    }

    private function isFilled(array $record, array $columns): bool
    {
        foreach ($columns as $column) {
            if (!isset($record[$column]) || trim((string) $record[$column]) === '') {
                return false;
            }
        }

        return true;
    }

    private function normalizePaymentMethod(mixed $value): string
    {
        $paymentMethod = strtoupper(trim((string) ($value ?? '')));

        return $paymentMethod !== '' ? $paymentMethod : 'CASH';
    }

    /**
     * @param  array<string, array<string, mixed>>  $transactions
     * @return array<int, array{receipt_no: string, reason: string, products: array<int, string>}>
     */
    private function rejectReceiptsWithUnknownProducts(array &$transactions): array
    {
        if (empty($transactions)) {
            return [];
        }

        $productNames = collect($transactions)
            ->flatMap(fn ($transaction) => array_column($transaction['items'], 'product_name'))
            ->unique()
            ->values()
            ->all();
        $existingProductNames = $this->repository->getExistingProductNames($productNames);
        $missingProductNames = array_values(array_diff($productNames, $existingProductNames));

        if (empty($missingProductNames)) {
            return [];
        }

        $missingLookup = array_flip($missingProductNames);
        $rejectedReceipts = [];

        foreach ($transactions as $receiptNo => $transaction) {
            $missingInReceipt = collect($transaction['items'])
                ->pluck('product_name')
                ->filter(fn ($productName) => isset($missingLookup[$productName]))
                ->unique()
                ->values()
                ->all();

            if (!empty($missingInReceipt)) {
                $rejectedReceipts[] = [
                    'receipt_no' => $receiptNo,
                    'reason' => 'Produk tidak ditemukan di master data',
                    'products' => $missingInReceipt,
                ];
                unset($transactions[$receiptNo]);
            }
        }

        return $rejectedReceipts;
    }
}
