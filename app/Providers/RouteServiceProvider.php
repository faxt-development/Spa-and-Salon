<?php

namespace App\Providers;

use App\Models\Company;
use App\Models\Theme;
use App\Services\ThemeService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
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
        parent::boot();

        $this->themeService = app(ThemeService::class);
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
    /**
     * The ThemeService instance.
     *
     * @var \App\Services\ThemeService
     */
    protected $themeService;

    /**
     * Create a new route service provider instance.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return void
     */
    public function __construct($app)
    {
        parent::__construct($app);
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
                $this->applyDefaultTheme();
                return;
            }

            if (app()->runningInConsole()) {
                return;
            }

            // Skip theme logic for auth routes
            if (request()->is('login') || request()->is('register')) {
                $this->applyDefaultTheme();
                return;
            }

            // Check if we have a cached version
            $cacheKey = "company_theme:{$host}";
            $company = Cache::remember($cacheKey, now()->addDay(), function () use ($host) {
                return Company::with('theme')->where('domain', $host)->first();
            });

            if ($company) {
                // Share company with all views
                View::share('company', $company);

                // Apply the company's theme or default theme if none set
                $theme = $company->theme ?? $this->themeService->getDefaultTheme();
                $this->applyTheme($theme);

                // Make company available in the request
                $this->app->instance('currentCompany', $company);

                // Also add to request attributes for controller access
                if (request()) {
                    request()->attributes->set('company', $company);
                }
            } else {
                // If no company found for the domain, apply default theme
                $this->applyDefaultTheme();
            }
        } catch (\Exception $e) {
            // Silently fail to prevent breaking the application
            logger()->error('Error setting theme and company context: ' . $e->getMessage());
            $this->applyDefaultTheme();
        }
    }

    /**
     * Apply the given theme to the application
     *
     * @param  \App\Models\Theme  $theme
     * @return void
     */
    protected function applyTheme(Theme $theme): void
    {
        // Share theme data with all views
        View::share('theme', $theme);

        // Set CSS variables for the theme
        $cssVariables = [
            '--primary-color' => $theme->primary_color,
            '--secondary-color' => $theme->secondary_color,
            '--accent-color' => $theme->accent_color,
            '--text-color' => $theme->text_color,
        ];

        View::share('cssVariables', $cssVariables);

        // Also make available in config for non-view usage
        config(['app.theme' => [
            'primary' => $theme->primary_color,
            'secondary' => $theme->secondary_color,
            'accent' => $theme->accent_color,
            'text' => $theme->text_color,
        ]]);
    }

    /**
     * Apply the default theme to the application
     *
     * @return void
     */
    protected function applyDefaultTheme(): void
    {
        $defaultTheme = $this->themeService->getDefaultTheme();
        $this->applyTheme($defaultTheme);
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
