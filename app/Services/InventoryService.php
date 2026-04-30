<?php

namespace App\Services;

use App\Repositories\InventoryRepository;
use App\Repositories\DashboardRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class InventoryService
{
    protected $repo;
    protected $dashboardRepo;

    // Lakukan Dependency Injection untuk memanggil kedua Repository
    public function __construct(InventoryRepository $repo, DashboardRepository $dashboardRepo)
    {
        $this->repo = $repo;
        $this->dashboardRepo = $dashboardRepo;
    }

    public function getInventoryAlerts()
    {
        $items = $this->repo->getAllItems();

        // ==========================================
        // DYNAMIC SMA (SIMPLE MOVING AVERAGE) LOGIC
        // ==========================================

        // 1. Ambil data total transaksi 30 hari ke belakang
        $startDate = Carbon::now()->subDays(30);

        // PERBAIKAN: Gunakan fungsi baru yang spesifik dari DashboardRepo, BUKAN getKpiStats
        $totalTrx30Days = $this->dashboardRepo->getTotalTransactionsSince($startDate);

        // 2. Hitung Rata-rata Harian
        // Cegah pembagian dengan nol jika data belum ada sama sekali
        $dailyAverage = $totalTrx30Days > 0 ? ($totalTrx30Days / 30) : 0;

        // 3. Prediksi total transaksi untuk 1 Minggu (7 Hari) ke depan
        $totalForecastTrx = round($dailyAverage * 7);

        // ==========================================

        $alerts = $items->map(function ($item) use ($totalForecastTrx) {
            // Prediksi penggunaan bahan = Forecast Transaksi x Pemakaian per Transaksi
            $predictedUsage = $totalForecastTrx * $item->usage_per_trx;

            // Estimasi sisa stok setelah seminggu ke depan
            $sisaStok = $item->current_stock - $predictedUsage;

            // Logika Penentu Status:
            // Jika estimasi sisa stok <= batas minimum, maka peringatan KRITIS!
            $status = $sisaStok <= $item->min_stock ? 'Kritis' : 'Aman';

            return [
                'id' => $item->id,
                'item_name' => $item->item_name,
                'current_stock' => $item->current_stock,
                'unit' => $item->unit,
                'predicted_usage' => round($predictedUsage, 2),
                'status' => $status
            ];
        });

        return [
            'forecast_next_week_trx' => $totalForecastTrx,
            'inventory_alerts' => $alerts
        ];
    }

    public function addManualStock($id, $quantity)
    {
        $item = $this->repo->addStock($id, $quantity);

        // Hancurkan cache agar data alert inventory diperbarui
        Cache::flush();

        return $item;
    }

    public function createNewItem(array $data)
    {
        return $this->repo->createItem($data);
    }
}
