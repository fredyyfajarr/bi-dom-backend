<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    protected $service;

    public function __construct(ReportService $service)
    {
        $this->service = $service;
    }

    public function exportPdf(Request $request)
        {
            try {
                $days = $request->query('days', 30);

                $pdfOutput = $this->service->generateMonthlyPdf($days);

                return response($pdfOutput, 200)
                    ->header('Content-Type', 'application/pdf')
                    ->header('Content-Disposition', 'attachment; filename="DOM_Report_Last_' . $days . '_Days.pdf"');

            } catch (\Exception $e) {
                // Jika meledak, berikan pesan error JSON yang jelas!
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mencetak PDF: ' . $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ], 500);
            }
        }
}
