<?php

namespace App\Application\Imports;

class CsvTransactionImportResult
{
    public function __construct(
        public readonly int $transactionCount,
        public readonly int $detailCount,
        public readonly string $format,
        public readonly int $skippedCount = 0,
        public readonly array $skippedReceipts = [],
        public readonly int $rejectedCount = 0,
        public readonly array $rejectedReceipts = [],
    ) {
    }
}
