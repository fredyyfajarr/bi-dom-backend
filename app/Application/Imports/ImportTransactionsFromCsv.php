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

        throw new Exception('Header CSV tidak dikenali. Gunakan format receipt_no,trx_date,total_amount atau receipt_no,trx_date,product_name,qty,subtotal.');
    }

    private function importSimpleRows(array $header, array $rows): CsvTransactionImportResult
    {
        $transactions = [];
        foreach ($rows as $row) {
            $record = $this->combineRow($header, $row);
            if (!$this->isFilled($record, self::SIMPLE_COLUMNS)) {
                continue;
            }

            $transactions[] = [
                'receipt_no' => trim($record['receipt_no']),
                'trx_date' => trim($record['trx_date']),
                'total_amount' => (float) $record['total_amount'],
            ];
        }

        if (empty($transactions)) {
            throw new Exception('Tidak ada transaksi valid di CSV.');
        }

        $count = $this->repository->saveSimpleTransactions($transactions);

        return new CsvTransactionImportResult($count, 0, 'simple');
    }

    private function importItemizedRows(array $header, array $rows): CsvTransactionImportResult
    {
        $transactions = [];
        foreach ($rows as $row) {
            $record = $this->combineRow($header, $row);
            if (!$this->isFilled($record, self::ITEMIZED_COLUMNS)) {
                continue;
            }

            $receiptNo = trim($record['receipt_no']);
            if (!isset($transactions[$receiptNo])) {
                $transactions[$receiptNo] = [
                    'receipt_no' => $receiptNo,
                    'trx_date' => trim($record['trx_date']),
                    'items' => [],
                ];
            }

            $transactions[$receiptNo]['items'][] = [
                'product_name' => trim($record['product_name']),
                'qty' => (int) $record['qty'],
                'subtotal' => (float) $record['subtotal'],
            ];
        }

        if (empty($transactions)) {
            throw new Exception('Tidak ada detail transaksi valid di CSV.');
        }

        $detailCount = array_sum(array_map(fn ($transaction) => count($transaction['items']), $transactions));
        $transactionCount = $this->repository->saveItemizedTransactions(array_values($transactions));

        return new CsvTransactionImportResult($transactionCount, $detailCount, 'itemized');
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
        return array_map(fn ($column) => strtolower(trim((string) $column)), $header);
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
}
