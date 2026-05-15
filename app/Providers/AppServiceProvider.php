<?php

namespace App\Providers;

use App\Domain\Contracts\TransactionImportRepositoryInterface;
use App\Infrastructure\Persistence\EloquentTransactionImportRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            TransactionImportRepositoryInterface::class,
            EloquentTransactionImportRepository::class,
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
