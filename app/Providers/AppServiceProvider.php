<?php

namespace App\Providers;

use App\Models\Company;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        // Share company name with all views
        View::composer('*', function ($view) {
            $companyName = 'Salon';
            
            if (Auth::check() && Auth::user()->company) {
                $companyName = Auth::user()->company->name ?: 'Salon';
            }
            
            $view->with('companyName', $companyName);
        });

        // Trust all proxies or specify your proxy IPs
        Request::setTrustedProxies(
            [request()->getClientIp()],
            SymfonyRequest::HEADER_X_FORWARDED_FOR |
            SymfonyRequest::HEADER_X_FORWARDED_HOST |
            SymfonyRequest::HEADER_X_FORWARDED_PORT |
            SymfonyRequest::HEADER_X_FORWARDED_PROTO |
            SymfonyRequest::HEADER_X_FORWARDED_AWS_ELB
        );

        // Force HTTPS if your app should always use HTTPS
        if ($this->app->environment('production') || $this->app->environment('staging')) {
            URL::forceScheme('https');
            $this->app['request']->server->set('HTTPS', 'on');
        }
    }
}

