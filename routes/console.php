<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Services\ForecastingService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {
    $service = new ForecastingService();
    $service->calculateDailyTransactionSMA(7);
})->daily();

Schedule::command('domhub:daily-recap')->dailyAt('23:59');
