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

   public function generateMonthlyPdf($days)
    {
        $startDate = \Carbon\Carbon::now()->subDays($days);
        $data = $this->repo->getReportData($startDate);
        $data['days'] = $days;
        $data['generated_at'] = \Carbon\Carbon::now()->format('d M Y H:i');

        // Langsung panggil view, biarkan Blade yang mengurus gambarnya
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.daily', $data);

        return $pdf->output();
    }
}
