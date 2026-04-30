<?php

namespace App\Services;

use App\Repositories\ReportRepository;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ReportService
{
    protected $repo;

    public function __construct(ReportRepository $repo)
    {
        $this->repo = $repo;
    }

    // Di dalam ReportService.php
public function generateMonthlyPdf($days)
{
    $startDate = Carbon::now()->subDays($days);
    $data = $this->repo->getReportData($startDate);
    $data['days'] = $days;
    $data['generated_at'] = Carbon::now()->format('d M Y H:i');

    // Pastikan string 'reports.daily' sesuai dengan folder & nama file
    $pdf = Pdf::loadView('reports.daily', $data);

    return $pdf->output();
}
}
