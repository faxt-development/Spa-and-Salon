<?php

namespace App\Providers;

use App\Services\RevenueRecognitionService;
use Illuminate\Support\ServiceProvider;

class RevenueRecognitionServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(RevenueRecognitionService::class, function ($app) {
            return new RevenueRecognitionService();
        });

        // Register an alias for the facade
        $this->app->alias(RevenueRecognitionService::class, 'revenue-recognition');
        
        // Merge config
        $this->mergeConfigFrom(
            __DIR__.'/../../config/revenue_recognition.php', 'revenue_recognition'
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish configuration
        $this->publishes([
            __DIR__.'/../../config/revenue_recognition.php' => config_path('revenue_recognition.php'),
        ], 'config');
        
        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                \App\Console\Commands\ProcessRevenueRecognition::class,
            ]);
        }
    }
}
