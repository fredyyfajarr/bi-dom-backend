<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ReportExportRequest;
use App\Services\ReportService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ReportController extends Controller
{
    public function __construct(private readonly ReportService $service) {}

    public function exportPdf(ReportExportRequest $request): Response|JsonResponse
    {
        try {
            $days = $request->days();
            $pdfOutput = $this->service->generateMonthlyPdf($days);

            return response($pdfOutput, 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="DOM_Report_Last_'.$days.'_Days.pdf"');
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mencetak PDF: '.$e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500);
        }
    }
}
