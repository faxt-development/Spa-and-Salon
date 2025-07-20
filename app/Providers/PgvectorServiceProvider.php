<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Pgvector\Laravel\Schema\Blueprint;
use Pgvector\Laravel\PgvectorServiceProvider as VendorPgvectorServiceProvider;

class PgvectorServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register the vendor's PgvectorServiceProvider
        $this->app->register(VendorPgvectorServiceProvider::class);
    }


}
