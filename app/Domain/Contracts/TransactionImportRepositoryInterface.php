<?php

namespace App\Domain\Contracts;

interface TransactionImportRepositoryInterface
{
    public function saveSimpleTransactions(array $transactions): int;

    public function saveItemizedTransactions(array $transactions): int;

    /**
     * @param  array<int, string>  $receiptNos
     * @return array<int, string>
     */
    public function getExistingReceiptNos(array $receiptNos): array;
}
