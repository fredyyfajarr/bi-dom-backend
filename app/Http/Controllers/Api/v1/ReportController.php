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
        // Tangkap parameter 'days' dari React (Frontend)
        $days = $request->query('days', 30);

        // Panggil service yang mengembalikan output PDF raw
        $pdfOutput = $this->service->generateMonthlyPdf($days);

        // Kembalikan sebagai response download PDF
        return response($pdfOutput, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="DOM_Report_Last_' . $days . '_Days.pdf"');
    }
}
