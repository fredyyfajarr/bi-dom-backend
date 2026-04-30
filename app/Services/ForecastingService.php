<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class ForecastingService
{
    /**
     * Menghitung Simple Moving Average (SMA) jumlah transaksi
     * n = Jumlah hari ke belakang (default 7 hari)
     */
    public function calculateDailyTransactionSMA(int $days = 7): int
    {
        // Ambil jumlah transaksi harian selama n hari terakhir
        $dailyTransactions = Transaction::select(DB::raw('COUNT(*) as count'))
            ->groupBy(DB::raw('DATE(trx_date)'))
            ->orderBy(DB::raw('DATE(trx_date)'), 'desc')
            ->limit($days)
            ->pluck('count');

        if ($dailyTransactions->isEmpty()) {
            return 0;
        }

        // Kalkulasi SMA: (A1 + A2 + ... + An) / n
        $sum = $dailyTransactions->sum();
        $sma = $sum / $dailyTransactions->count();

        return (int) round($sma);
    }
}
