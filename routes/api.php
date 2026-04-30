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

    // Public Route (Bisa diakses sebelum login)
    Route::post('/login', [AuthController::class, 'login']);

    // Protected Routes (Harus login dengan token Sanctum)
    Route::middleware('auth:sanctum')->group(function () {

        // Cek User Auth
        Route::get('/user', function (Request $request) {
            return $request->user();
        });

        // ==========================================
        // MODULE: MASTER PRODUCT & RECIPES
        // ==========================================
        Route::apiResource('products', ProductController::class); // <-- 2. TARUH RUTENYA DI SINI

        // ==========================================
        // MODULE: DASHBOARD
        // ==========================================
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

        // ==========================================
        // MODULE: IMPORT (Data Transaksi)
        // ==========================================
        Route::post('/import', [ImportController::class, 'uploadCsv']);

        // ==========================================
        // MODULE: INVENTORY (SMA & Stock)
        // ==========================================
        Route::prefix('inventory')->group(function () {
            Route::get('/alerts', [InventoryController::class, 'getAlerts']);
            Route::get('/list', [InventoryController::class, 'getInventoryList']);
            Route::post('/update-stock', [InventoryController::class, 'updateStock']);
            Route::post('/items', [InventoryController::class, 'store']);
        });

        // ==========================================
        // MODULE: INVOICES
        // ==========================================

        Route::prefix('invoices')->group(function () {
            Route::get('/', [InvoiceController::class, 'index']);
        });

        // ==========================================
        // MODULE: REPORTS
        // ==========================================
        Route::get('/reports/export-pdf', [ReportController::class, 'exportPdf']);

    });
});
