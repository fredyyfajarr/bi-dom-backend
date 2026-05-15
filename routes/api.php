<?php

use App\Http\Controllers\Api\v1\AuthController;
use App\Http\Controllers\Api\v1\DashboardController;
use App\Http\Controllers\Api\v1\ImportController;
use App\Http\Controllers\Api\v1\InventoryController;
use App\Http\Controllers\Api\v1\InvoiceController;
use App\Http\Controllers\Api\v1\ProductController;
use App\Http\Controllers\Api\v1\ReportController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route custom untuk sistem Business Intelligence DOM Social Hub
Route::prefix('v1')->group(function () {

    // ==========================================
    // PUBLIC ROUTES (Bisa diakses tanpa login)
    // ==========================================
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');


    // ==========================================
    // PROTECTED ROUTES (Wajib login Sanctum)
    // ==========================================
    Route::middleware('auth:sanctum')->group(function () {

        // -----------------------------------------------------
        // AREA UMUM: BISA DIAKSES KASIR & MANAGER
        // -----------------------------------------------------

        // Cek Data User Aktif (Dibutuhkan Frontend untuk ngecek Role)
        Route::get('/user', function (Request $request) {
            return $request->user();
        });
        Route::post('/logout', [AuthController::class, 'logout']);

        // MODULE: IMPORT (Data Transaksi Harian)
        Route::post('/import', [ImportController::class, 'uploadCsv']);
        Route::post('/import-csv', [ImportController::class, 'uploadCsv']);

        // MODULE: INVOICES (Lihat Daftar Transaksi)
        Route::prefix('invoices')->group(function () {
            Route::get('/', [InvoiceController::class, 'index']);
        });


        // -----------------------------------------------------
        // AREA VIP: HANYA BISA DIAKSES MANAGER
        // Jika kasir tembak API ini, otomatis dapat Error 403
        // -----------------------------------------------------
        Route::middleware('role:manager')->group(function () {

            // MODULE: MASTER PRODUCT & RECIPES
            Route::apiResource('products', ProductController::class);

            // MODULE: DASHBOARD (Analytics & BI)
            Route::prefix('dashboard')->group(function () {
                Route::get('/available-years', [DashboardController::class, 'getAvailableYears']);
                Route::get('/categories-list', [DashboardController::class, 'getCategoriesList']);
                Route::get('/kpi', [DashboardController::class, 'getKpi']);
                Route::get('/charts', [DashboardController::class, 'getCharts']);
                Route::get('/transactions', [DashboardController::class, 'getTransactions']);
                Route::get('/transactions/{id}', [DashboardController::class, 'getTransactionDetail']);
                Route::get('/top-products', [DashboardController::class, 'getTopProducts']);
                Route::get('/inventory-alerts', [DashboardController::class, 'getLowStockAlerts']);
                Route::get('/donut-chart', [DashboardController::class, 'getDonutData']);
                Route::get('/advanced-analytics', [DashboardController::class, 'getAdvancedAnalytics']);
                Route::get('/peak-hour-detail', [DashboardController::class, 'getPeakHourDetail']);
            });

            // MODULE: INVENTORY (SMA & Stock)
            Route::prefix('inventory')->group(function () {
                Route::get('/alerts', [InventoryController::class, 'getAlerts']);
                Route::get('/list', [InventoryController::class, 'getInventoryList']);
                Route::post('/update-stock', [InventoryController::class, 'updateStock']);
                Route::post('/items', [InventoryController::class, 'store']);
            });

            // MODULE: REPORTS
            Route::get('/reports/export-pdf', [ReportController::class, 'exportPdf']);

        });

    });
});
