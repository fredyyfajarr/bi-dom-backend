<?php

namespace App\Services;

use App\Repositories\ReportRepository;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ReportService
{
    public function __construct(private readonly ReportRepository $repo) {}

    public function generateMonthlyPdf(int $days): string
    {
        $startDate = Carbon::now()->subDays($days);
        $data = $this->repo->getReportData($startDate);
        $data['days'] = $days;
        $data['generated_at'] = Carbon::now()->format('d M Y H:i');

        $pdf = Pdf::loadView('reports.daily', $data);

        return $pdf->output();
    }
}
