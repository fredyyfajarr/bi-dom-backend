<?php

namespace App\Application\Imports;

class CsvTransactionImportResult
{
    public function __construct(
        public readonly int $transactionCount,
        public readonly int $detailCount,
        public readonly string $format,
    ) {
    }
}
