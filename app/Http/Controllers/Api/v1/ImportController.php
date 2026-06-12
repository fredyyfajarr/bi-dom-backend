<?php

namespace App\Http\Controllers\Api\v1;

use App\Application\Imports\ImportTransactionsFromCsv;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ImportCsvRequest;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\JsonResponse;

class ImportController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly ImportTransactionsFromCsv $importTransactions,
    ) {}

    public function uploadCsv(ImportCsvRequest $request): JsonResponse
    {
        $uploadedFile = $request->uploadedCsv();
        if (! $uploadedFile) {
            return $this->errorResponse('File CSV wajib diunggah.', 422);
        }

        try {
            $result = $this->importTransactions->execute($uploadedFile->getRealPath());

            $message = "{$result->transactionCount} transaksi berhasil diproses.";
            if ($result->skippedCount > 0) {
                $message .= " {$result->skippedCount} transaksi di-skip (receipt sudah ada).";
            }
            if ($result->rejectedCount > 0) {
                $message .= " {$result->rejectedCount} transaksi ditolak (data tidak cocok master).";
            }

            return $this->successResponse(
                [
                    'format' => $result->format,
                    'transactions' => $result->transactionCount,
                    'details' => $result->detailCount,
                    'skipped_count' => $result->skippedCount,
                    'skipped_receipts' => $result->skippedReceipts,
                    'rejected_count' => $result->rejectedCount,
                    'rejected_receipts' => $result->rejectedReceipts,
                ],
                $message,
            );
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }
}
