<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DashboardService;
use Illuminate\Support\Facades\Log;

class GenerateDailyRecap extends Command
{
    // Nama perintah di terminal
    protected $signature = 'domhub:daily-recap';

    // Deskripsi singkat
    protected $description = 'Merekap total transaksi dan pendapatan harian DOM Social Hub secara otomatis';

    public function handle(DashboardService $dashboardService)
    {
        $this->info('Memulai proses rekap data harian...');

        $data = $dashboardService->getDashboardData(1);

        $revenue = $data['kpi']['revenue'] ?? 0;
        $trxCount = $data['kpi']['transaction_count'] ?? 0;
        $date = now()->format('Y-m-d');

        // Gunakan Log::info standar Laravel 13
        Log::info("REKAP HARIAN [$date] - Transaksi: $trxCount | Pendapatan: Rp " . number_format($revenue, 0, ',', '.'));

        $this->info("Berhasil merekap $trxCount transaksi dengan total Rp " . number_format($revenue, 0, ',', '.'));
    }
}
