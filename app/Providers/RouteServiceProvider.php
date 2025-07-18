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

        // Set this to not run if this is not a web request
        if (app()->runningInConsole()) {
            return;
        }

        // Handle subscription check for authenticated users
        $this->checkSubscriptionStatus();

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
                // First try exact match
                $exactMatch = Company::with('theme')->where('domain', $host)->first();
                if ($exactMatch) {
                    return $exactMatch;
                }
                
                // If no exact match, look for domain in comma-separated list
                return Company::with('theme')
                    ->whereRaw("FIND_IN_SET(?, domain) > 0", [$host])
                    ->orWhereRaw("? LIKE CONCAT('%,', domain, ',%')", [",{$host},"])
                    ->orWhereRaw("? LIKE CONCAT(domain, ',%')", ["{$host},"])
                    ->orWhereRaw("? LIKE CONCAT('%,', domain)", [",{$host}"])
                    ->first();
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
     * Check if the authenticated user has an active subscription
     * and redirect to subscription page if not
     */
    protected function checkSubscriptionStatus()
    {
        // Skip if user is not authenticated - let the auth middleware handle this
        if (!auth()->check()) {
            return;
        }

        $user = auth()->user();
        $currentRoute = request()->route();
        // Skip if already on the subscription required page or auth routes
        $excludedRoutes = [
            'subscription.required',
            'logout',
            'pricing',
            'subscription.*',
            'billing.*',
            'login',
            'register',
            'password.*',
            'verification.*',
            'onboarding.*',
            'home'
        ];
        
        // If we can't determine the route name, allow the request to continue
        if (!$currentRoute || !$currentRoute->getName()) {
            return;
        }

        foreach ($excludedRoutes as $excludedRoute) {
            if (str_is($excludedRoute, $currentRoute->getName())) {
                return;
            }
        }
info('checking subscription');
        // Check if user has an active subscription
        if (!$user->hasActiveSubscription()) {
            // Skip if this is an API request
            if (request()->expectsJson()) {
                return;
            }

            // Only redirect if we're not already on the subscription required page
            if ($currentRoute->getName() !== 'subscription.required') {
                // Use response()->redirectToRoute() which is safe to use in service providers
                return response()->redirectToRoute('subscription.required')->sendHeaders();
            }
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
