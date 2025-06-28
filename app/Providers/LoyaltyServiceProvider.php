<?php

namespace App\Providers;

use App\Models\Order;
use App\Observers\OrderObserver;
use Illuminate\Support\ServiceProvider;
use App\Services\LoyaltyService;
use App\Services\DiscountService;

class LoyaltyServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(LoyaltyService::class, function ($app) {
            return new LoyaltyService();
        });

        $this->app->singleton(DiscountService::class, function ($app) {
            return new DiscountService();
        });
    }

    public function boot()
    {
        Order::observe(OrderObserver::class);
    }
}
