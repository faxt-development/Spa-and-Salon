<?php

namespace App\Providers;

use App\Services\OrderService;
use App\Services\TaxCalculationService;
use Illuminate\Support\ServiceProvider;

class TaxServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register TaxCalculationService
        $this->app->singleton(TaxCalculationService::class, function ($app) {
            return new TaxCalculationService();
        });
        
        // Register OrderService
        $this->app->singleton(OrderService::class, function ($app) {
            return new OrderService(
                $app->make(TaxCalculationService::class)
            );
        });
        
        // Aliases for convenience
        $this->app->alias(TaxCalculationService::class, 'tax');
        $this->app->alias(OrderService::class, 'order-service');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
