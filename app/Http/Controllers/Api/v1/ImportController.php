<?php

namespace App\Http\Controllers\Api\v1;

use App\Application\Imports\ImportTransactionsFromCsv;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use Exception;

class ImportController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly ImportTransactionsFromCsv $importTransactions,
    )
    {
    }

    public function uploadCsv(Request $request)
    {
        $uploadedFile = $request->file('file') ?? $request->file('csv_file');
        if (!$uploadedFile) {
            return $this->errorResponse('File CSV wajib diunggah.', 422);
        }

        if (!in_array($uploadedFile->getClientOriginalExtension(), ['csv', 'txt'])) {
            return $this->errorResponse('File harus berformat csv atau txt.', 422);
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
