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

            return $this->successResponse(
                [
                    'format' => $result->format,
                    'transactions' => $result->transactionCount,
                    'details' => $result->detailCount,
                ],
                "File diterima. {$result->transactionCount} transaksi berhasil diproses. Dashboard telah disegarkan!"
            );
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }
}
