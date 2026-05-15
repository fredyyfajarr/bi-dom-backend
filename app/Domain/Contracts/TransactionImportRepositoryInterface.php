<?php

namespace App\Domain\Contracts;

interface TransactionImportRepositoryInterface
{
    public function saveSimpleTransactions(array $transactions): int;

    public function saveItemizedTransactions(array $transactions): int;
}
