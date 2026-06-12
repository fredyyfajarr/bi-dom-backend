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
        $data = $this->repo->getReportData($startDate, Carbon::now());
        $data['days'] = $days;
        $data['filter_label'] = 'Last '.$days.' Days';
        $data['generated_at'] = Carbon::now()->format('d M Y H:i');

        $pdf = Pdf::loadView('reports.daily', $data);

        return $pdf->output();
    }

    /**
     * @param  array{days: int, year: int|null, period: string, monthIndex: int|null, start_date: string|null, end_date: string|null, category_id: int|null}  $filters
     */
    public function generateDashboardPdf(array $filters): string
    {
        [$startDate, $endDate, $filterLabel] = $this->resolveDateRange($filters);
        $data = $this->repo->getReportData($startDate, $endDate, $filters['category_id']);

        $categoryName = $filters['category_id']
            ? $this->repo->getCategoryName($filters['category_id'])
            : null;

        $data['days'] = $filters['days'];
        $data['filter_label'] = $filterLabel.($categoryName ? ' | Category: '.$categoryName : ' | All Categories');
        $data['generated_at'] = Carbon::now()->format('d M Y H:i');

        $pdf = Pdf::loadView('reports.daily', $data);

        return $pdf->output();
    }

    /**
     * @param  array{days: int, year: int|null, period: string, monthIndex: int|null, start_date: string|null, end_date: string|null, category_id: int|null}  $filters
     * @return array{Carbon, Carbon, string}
     */
    private function resolveDateRange(array $filters): array
    {
        if ($filters['start_date'] && $filters['end_date']) {
            $startDate = Carbon::parse($filters['start_date'])->startOfDay();
            $endDate = Carbon::parse($filters['end_date'])->endOfDay();

            return [$startDate, $endDate, $startDate->format('d M Y').' - '.$endDate->format('d M Y')];
        }

        if ($filters['year']) {
            if ($filters['period'] === 'month' && $filters['monthIndex'] !== null) {
                $startDate = Carbon::create($filters['year'], $filters['monthIndex'] + 1, 1)->startOfMonth();
                $endDate = $startDate->copy()->endOfMonth();

                return [$startDate, $endDate, $startDate->format('F Y')];
            }

            $startDate = Carbon::create($filters['year'], 1, 1)->startOfYear();
            $endDate = Carbon::create($filters['year'], 12, 31)->endOfYear();

            return [$startDate, $endDate, (string) $filters['year']];
        }

        $startDate = Carbon::now()->subDays($filters['days']);
        $endDate = Carbon::now();

        return [$startDate, $endDate, 'Last '.$filters['days'].' Days'];
    }
}
