<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use App\Models\Company;
use Illuminate\Support\Facades\View;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * @var string
     */
    public const HOME = '/dashboard';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });

        // Set theme and company context for the request
        $this->setThemeAndCompanyContext();
    }

    /**
     * Set the theme and company context for the request
     */
    protected function setThemeAndCompanyContext()
    {
        try {
            // Get the current host (without port)
            $host = request()->getHost();
            $host = preg_replace('/:\d+$/', '', $host);

            // Get main domain from config
            $appUrl = config('app.url');
            $mainDomain = parse_url($appUrl, PHP_URL_HOST) ?: $appUrl;

            // Skip if this is the main domain
            if ($host === $mainDomain) {
                return;
            }

            if (app()->runningInConsole()) {
                return;
            }

            // Skip theme logic for auth routes
            if (request()->is('login') || request()->is('register')) {
                    return;
                }


            // Check if we have a cached version
            $cacheKey = "company_theme:{$host}";
            $company = Cache::remember($cacheKey, now()->addDay(), function () use ($host) {
                return Company::where('domain', $host)->first();
            });

            if ($company) {
                // Share company with all views
                View::share('company', $company);

                // Set the theme
                if (!empty($company->theme_settings)) {
                    config(['app.theme' => $company->theme_settings]);
                }

                // Make company available in the request
                $this->app->instance('currentCompany', $company);

                // Also add to request attributes for controller access
                if (request()) {
                    request()->attributes->set('company', $company);
                }

                // Share with all views (in case view is rendered before this point)
                View::share('company', $company);
            }
        } catch (\Exception $e) {
            // Silently fail to prevent breaking the application
        }
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
