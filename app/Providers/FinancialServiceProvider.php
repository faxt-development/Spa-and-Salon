<?php

namespace App\Providers;

use App\Services\FinancialReportingService;
use Illuminate\Support\ServiceProvider;

class FinancialServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(FinancialReportingService::class, function ($app) {
            return new FinancialReportingService();
        });

        // Register an alias for the facade
        $this->app->alias(FinancialReportingService::class, 'financial-reports');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish configuration file if needed in the future
        // $this->publishes([
        //     __DIR__.'/../../config/financial.php' => config_path('financial.php'),
        // ], 'config');
    }
}
