<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache; // Wajib ada untuk hapus cache
use App\Services\ImportService;
use App\Traits\ApiResponse;
use Exception;

class ImportController extends Controller
{
    use ApiResponse; // Memanggil trait standarisasi JSON

    protected $service;

    public function __construct(ImportService $service)
    {
        $this->service = $service;
    }

    public function uploadCsv(Request $request)
    {
        // Validasi file harus ada dan formatnya csv/txt
        $request->validate([
            'file' => 'required|file|mimes:csv,txt'
        ]);

        try {
            // 1. Proses Import CSV lewat Service
            $count = $this->service->processCsv($request->file('file'));

            // 2. JURUS NUKE: Hancurkan memori cache Dashboard lama!
            // Sesuaikan nama key-nya dengan yang ada di DashboardService Anda
            Cache::forget('kpi_stats_7');
            Cache::forget('kpi_stats_30');
            Cache::forget('sales_chart_7');
            Cache::forget('sales_chart_30');
            Cache::forget('latest_trx_7');
            Cache::forget('latest_trx_30');
            Cache::forget('top_products_7');
            Cache::forget('top_products_30');
            // Jika Anda pakai filter 90 hari / 365 hari, tambahkan juga di sini.

            // 3. Kembalikan Response Sukses
            return $this->successResponse(
                null,
                "File diterima. $count data berhasil diproses. Dashboard telah disegarkan!"
            );

        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }
}
