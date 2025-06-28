<?php

namespace App\Providers;

use App\Http\Middleware\LogHttpRequests;
use App\Services\AuditLogService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Http\Kernel;

class AuditLogServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/audit.php', 'audit'
        );

        $this->app->singleton(AuditLogService::class, function ($app) {
            return new AuditLogService();
        });

        // Register the alias for facade support
        $this->app->alias(AuditLogService::class, 'audit-log');
    }

    /**
     * Bootstrap services.
     */
    public function boot(Kernel $kernel): void
    {
        // Publish the config file
        $this->publishes([
            __DIR__.'/../../config/audit.php' => config_path('audit.php'),
        ], 'config');

        // Publish migrations
        $this->publishes([
            __DIR__.'/../../database/migrations/2025_06_28_103300_create_audit_logs_table.php' => 
                database_path('migrations/'.date('Y_m_d_His', time()).'_create_audit_logs_table.php'),
        ], 'migrations');

        // Register the middleware globally or for web/api routes
        if (config('audit.log_http_requests', true)) {
            $kernel->pushMiddleware(LogHttpRequests::class);
        }
    }
}
