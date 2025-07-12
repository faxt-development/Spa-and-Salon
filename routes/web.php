<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\InventoryController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GiftCardWebController;
use App\Http\Controllers\Inventory\CategoryController;
use App\Http\Controllers\Inventory\ProductController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\EmailCampaignController;
use App\Http\Controllers\EmailTrackingController;
use App\Http\Controllers\EmailMarketingDashboardController;
use App\Http\Controllers\DripCampaignController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\GdprController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\Admin\OnboardingChecklistController;

// Public routes
Route::get('/', [\App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Test theme route
Route::get('/theme-test', function () {
    $theme = app(\App\Services\ThemeService::class)->getCurrentTheme();
    $company = request()->attributes->get('company');

    return view('theme-test', [
        'theme' => $theme,
        'company' => $company,
        'pageTitle' => 'Theme Test Page',
    ]);
})->name('theme.test');

// Pricing page route
Route::get('/pricing', [\App\Http\Controllers\PricingController::class, 'index'])->name('pricing');

// Success page after checkout
Route::get('/success', function() {
    return view('success');
})->name('success');

// Static Pages
Route::controller(PageController::class)->group(function () {
    Route::get('/privacy', 'privacy')->name('privacy');
    Route::get('/terms', 'terms')->name('terms');
});

// Contact Routes
Route::controller(ContactController::class)->group(function () {
    Route::get('/contact', 'show')->name('contact');
    Route::post('/contact', 'submit')->name('contact.submit');
});

// GDPR Compliance
Route::get('/gdpr', [GdprController::class, 'index'])->name('gdpr');

// Press
Route::get('/press', [PageController::class, 'press'])->name('press');

// Onboarding routes - require authentication and check onboarding status
Route::middleware(['auth:web', 'check.onboarding'])
    ->prefix('onboarding')
    ->name('onboarding.')
    ->group(function () {
        Route::get('/start', [OnboardingController::class, 'showStart'])->name('start');
        Route::get('/user', [OnboardingController::class, 'showUserForm'])->name('user-form');
        Route::post('/user', [OnboardingController::class, 'processUserForm'])->name('process-user');
        Route::get('/company', [OnboardingController::class, 'showCompanyForm'])->name('company-form');
        Route::post('/company', [OnboardingController::class, 'processCompanyForm'])->name('process-company');
        Route::get('/feature-tour', [OnboardingController::class, 'showFeatureTour'])->name('feature-tour');
        Route::post('/complete', [OnboardingController::class, 'complete'])->name('complete');
    });

// Test route for simulating onboarding (PROTECTED - DEVELOPMENT ONLY)
Route::middleware(['auth:web'])->get('/test-onboarding', function() {
    // Simulate a session ID from Stripe
    $sessionId = 'test_session_' . time();

    // Store in session
    session(['stripe_session_id' => $sessionId]);

    // Create a test user if not logged in
    if (!auth()->check()) {
        // Check if test user exists
        $testUser = \App\Models\User::where('email', 'test@example.com')->first();

        if (!$testUser) {
            // Create test user
            $testUser = \App\Models\User::create([
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => bcrypt('password'),
                'email_notifications' => true,
                'onboarding_completed' => false,
            ]);

            // Assign admin role
            $testUser->assignRole('admin');

            // Create test subscription
            $plan = \App\Models\Plan::first();
            if (!$plan) {
                $plan = \App\Models\Plan::create([
                    'name' => 'Test Plan',
                    'slug' => 'test-plan',
                    'stripe_plan_id' => 'test_plan_id',
                    'price' => 99.99,
                    'currency' => 'usd',
                    'is_active' => true,
                ]);
            }

            \App\Models\Subscription::create([
                'user_id' => $testUser->id,
                'plan_id' => $plan->id,
                'name' => $plan->name,
                'stripe_id' => 'test_subscription_' . time(),
                'stripe_status' => 'active',
                'stripe_price' => 'test_price_id',
                'quantity' => 1,
                'trial_ends_at' => now()->addDays(14),
                'status' => 'active',
                'billing_cycle' => $plan->billing_cycle,
                'next_billing_date' => now()->addDays(14),
            ]);
        }

        // Log in as test user
        auth()->login($testUser);
    }

    return redirect()->route('onboarding.start', ['session_id' => $sessionId]);
})->name('test-onboarding');


Route::get('/services', [ServiceController::class, 'index'])->name('services');

// Gift card routes
Route::controller(GiftCardWebController::class)->group(function () {
    Route::get('/gift-cards/purchase', 'purchaseForm')
        ->middleware('throttle:gift-card-purchase')
        ->name('gift-cards.purchase');

    Route::get('/gift-cards/history-user', 'historyUser')
        ->middleware('auth:web')
        ->name('gift-cards.history-user');

    Route::get('/gift-cards/history', 'history')
        ->middleware('auth:web')
        ->name('gift-cards.history');
});

// Debug route to check authentication and token status
Route::middleware(['auth:web'])->get('/debug-auth', function () {
    return response()->json([
        'user' => auth()->user(),
        'token_in_session' => session()->has('api_token') ? 'Yes' : 'No',
        'token_value' => session('api_token') ? substr(session('api_token'), 0, 10) . '...' : 'None',
        'session_id' => session()->getId(),
        'auth_check' => auth()->check() ? 'Yes' : 'No',
        'auth_id' => auth()->id(),
        'session_data' => session()->all(),
    ]);
});

// Dashboard routes
// Admin routes
Route::prefix('admin')->name('admin.')->middleware(['auth:web', 'role:admin'])->group(function () {
    // Report routes
    Route::prefix('reports')->name('reports.')->group(function () {
        // Service Category Reports
        Route::get('service-categories', [\App\Http\Controllers\Admin\ReportController::class, 'serviceCategories'])
            ->name('service.categories');
        Route::get('service-categories/data', [\App\Http\Controllers\Admin\ReportController::class, 'getServiceCategoryData'])
            ->name('service.categories.data');
        Route::get('service-performance/data', [\App\Http\Controllers\Admin\ReportController::class, 'getServicePerformanceData'])
            ->name('service.performance.data');
        Route::get('tax', [\App\Http\Controllers\Admin\ReportController::class, 'tax'])
            ->name('tax');
    });
    Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/todays-schedule', [\App\Http\Controllers\Admin\DashboardController::class, 'getTodaysSchedule'])->name('dashboard.todays-schedule');
    Route::get('/dashboard/alerts', [\App\Http\Controllers\Admin\DashboardController::class, 'getAlerts'])->name('dashboard.alerts');
    
    // Onboarding checklist route
    Route::get('/onboarding-checklist', [OnboardingChecklistController::class, 'show'])->name('onboarding-checklist');

    // Appointments routes - using consolidated AppointmentController
    Route::prefix('appointments')->name('appointments.')->group(function () {
        Route::get('/', [\App\Http\Controllers\AppointmentController::class, 'index'])->name('index');
        Route::get('/{appointment}', [\App\Http\Controllers\AppointmentController::class, 'show'])->name('show');
        Route::get('/{appointment}/edit', [\App\Http\Controllers\AppointmentController::class, 'edit'])->name('edit');
        Route::put('/{appointment}', [\App\Http\Controllers\AppointmentController::class, 'update'])->name('update');
        Route::delete('/{appointment}', [\App\Http\Controllers\AppointmentController::class, 'destroy'])->name('destroy');
    });
});

Route::middleware(['auth:web', \App\Http\Middleware\CheckOnboardingStatus::class])->group(function () {
    // Common authenticated user routes
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Appointment routes
    Route::post('/appointments/{appointment}/complete', [\App\Http\Controllers\AppointmentController::class, 'complete'])
        ->name('web.appointments.complete');

    // Public appointment booking routes can be added here without admin middleware

    // Gift card history for authenticated users
    Route::get('/gift-cards/history-user', [GiftCardWebController::class, 'history'])
        ->name('gift-cards.history-user');

    // Admin routes
    Route::middleware(['auth:web', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::resource('clients', 'App\Http\Controllers\Admin\ClientController');

        // Staff Management Routes
        Route::resource('staff', 'App\Http\Controllers\StaffController');
        Route::get('/roles-permissions', [App\Http\Controllers\StaffController::class, 'rolesAndPermissions'])->name('staff.roles');
        Route::post('/roles', [App\Http\Controllers\StaffController::class, 'storeRole'])->name('staff.roles.store');
        Route::put('/roles/{role}', [App\Http\Controllers\StaffController::class, 'updateRole'])->name('staff.roles.update');

        // Category reordering API routes
        Route::post('/categories/reorder', [CategoryController::class, 'reorder'])
            ->name('categories.reorder');
        Route::get('/categories/tree', [CategoryController::class, 'tree'])
            ->name('categories.tree');

        // Payroll Routes
        Route::prefix('payroll')->name('payroll.')->group(function () {
            Route::get('/records', [\App\Http\Controllers\PayrollController::class, 'payrollIndex'])->name('records.index');
            Route::get('/records/generate', [\App\Http\Controllers\PayrollController::class, 'payrollGenerate'])->name('records.generate');
            Route::get('/records/{id}', [\App\Http\Controllers\PayrollController::class, 'payrollShow'])->name('records.show');
            Route::get('/employees', [\App\Http\Controllers\PayrollController::class, 'employeeIndex'])->name('employees.index');
            Route::get('/employees/create', [\App\Http\Controllers\PayrollController::class, 'employeeCreate'])->name('employees.create');
            Route::get('/employees/{id}/edit', [\App\Http\Controllers\PayrollController::class, 'employeeEdit'])->name('employees.edit');
            Route::get('/time-clock', [\App\Http\Controllers\PayrollController::class, 'timeClockIndex'])->name('time-clock.index');
            Route::get('/time-clock/entry', [\App\Http\Controllers\PayrollController::class, 'timeClockEntry'])->name('time-clock.entry');
            Route::get('/reports', [\App\Http\Controllers\PayrollController::class, 'payrollReports'])->name('reports.index');

            // Reports
            Route::get('/reports/tax', [\App\Http\Controllers\Admin\ReportController::class, 'tax'])->name('reports.tax');
        });


        Route::get('/reports/sales', [\App\Http\Controllers\Admin\ReportController::class, 'sales'])->name('reports.sales');
        Route::get('/reports/payment-methods', [\App\Http\Controllers\Admin\ReportController::class, 'paymentMethods'])->name('reports.payment-methods');


          // Email Campaign Routes
    Route::resource('email-campaigns', EmailCampaignController::class);

    // Email Campaign Actions
    Route::prefix('email-campaigns')->name('email-campaigns.')->group(function () {
        Route::post('/{campaign}/send', [EmailCampaignController::class, 'send'])->name('send');
        Route::post('/{campaign}/cancel', [EmailCampaignController::class, 'cancel'])->name('cancel');
        Route::post('/{campaign}/duplicate', [EmailCampaignController::class, 'duplicate'])->name('duplicate');
    });

    // Email Campaign Additional Actions
    Route::prefix('email-campaigns')->name('email-campaigns.')->group(function () {
        Route::get('/{emailCampaign}/preview', [EmailCampaignController::class, 'preview'])->name('preview');
        Route::get('/{emailCampaign}/export', [EmailCampaignController::class, 'export'])->name('export');
    });

    // Email Tracking Routes (public routes that don't require authentication)
    Route::prefix('email')->name('email.')->group(function () {
        Route::get('/track/open/{token}.gif', [EmailTrackingController::class, 'trackOpen'])->name('track.open');
        Route::get('/track/click/{token}/{url}', [EmailTrackingController::class, 'trackClick'])->name('track.click');
        Route::get('/unsubscribe/{token}', [EmailTrackingController::class, 'unsubscribe'])->name('unsubscribe');
        Route::post('/resubscribe/{token}', [EmailTrackingController::class, 'resubscribe'])->name('resubscribe');
        Route::get('/preferences/{token}', [EmailTrackingController::class, 'preferences'])->name('preferences');
        Route::post('/preferences/{token}/update', [EmailTrackingController::class, 'updatePreferences'])->name('preferences.update');
    });

    });

        // POS Routes
    Route::prefix('pos')->name('pos.')->group(function () {
        Route::get('/', [PosController::class, 'index'])->name('index');
        Route::get('/receipt/{order}', [PosController::class, 'receipt'])->name('receipt');
        Route::get('/receipt/{order}/print', [PosController::class, 'printReceipt'])->name('receipt.print');

        // API endpoints for POS
        Route::get('/products', [PosController::class, 'getProducts']);
        Route::post('/process-payment', [PosController::class, 'processPayment'])->name('process-payment');
        Route::get('/gift-cards/purchase', [GiftCardWebController::class, 'purchaseForm'])->name('gift-cards.purchase');
      });

    // Inventory Management Routes
    Route::middleware(['auth:web', 'role:admin|staff'])->prefix('inventory')->name('inventory.')->group(function () {
        // Main inventory dashboard
        Route::get('/', [InventoryController::class, 'index'])->name('index');

        // Product resource routes
        Route::resource('products', InventoryController::class)->except(['index', 'show']);
        Route::get('/products/index', [ProductController::class, 'index'])
                    ->name('products.index');

        // Product details and actions
        Route::get('products/{product}/details', [InventoryController::class, 'show'])->name('products.details');
        Route::post('products/{product}/adjust', [InventoryController::class, 'updateInventory'])->name('products.adjust');

        // Inventory reports
        Route::get('reports/low-stock', [InventoryController::class, 'lowStockReport'])->name('reports.low-stock');

        // Bulk actions
        Route::post('bulk-actions', [InventoryController::class, 'bulkActions'])->name('bulk-actions');

        // Categories management
        Route::resource('categories', CategoryController::class)->except(['show']);

        // Bulk actions for categories
        Route::post('categories/bulk', [CategoryController::class, 'bulkActions'])
            ->name('categories.bulk');

        // API endpoint to get product count for a category
        Route::get('api/categories/{category}/products/count', [CategoryController::class, 'getProductCount'])
            ->name('categories.products.count');

        // Suppliers management
       // Route::resource('suppliers', 'App\Http\Controllers\SupplierController')->except(['show']);
    });

    // Appointments routes for both admin and staff
    Route::name('web.')->group(function () {
        Route::resource('appointments', 'App\Http\Controllers\AppointmentController')
            ;
    });

    Route::middleware(['auth:web', 'role:admin'])->group(function () {
        // Client Reports
        Route::prefix('reports/clients')->name('reports.clients.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\ClientReportController::class, 'index'])->name('index');
            Route::get('/export', [\App\Http\Controllers\Admin\ClientReportController::class, 'export'])->name('export');
            Route::get('/{client}/export', [\App\Http\Controllers\Admin\ClientReportController::class, 'exportSingle'])->name('export.single');
        });

        // Email Marketing Dashboard
        Route::get('/email-marketing/dashboard', [EmailMarketingDashboardController::class, 'index'])->name('email-marketing.dashboard');

    // Drip Campaign Routes
    Route::resource('drip-campaigns', DripCampaignController::class);

    // Test Export Page
    Route::get('/test-export', function () {
        return view('test-export');
    })->middleware('auth');

    // Export Routes
    Route::prefix('export')->name('export.')->middleware(['auth', 'admin'])->group(function () {
        // Excel Exports
        Route::get('excel/{type}', [ExportController::class, 'exportExcel'])
            ->name('excel')
            ->where('type', 'appointments|services|orders');

        // PDF Exports
        Route::get('pdf/{type}', [ExportController::class, 'exportPdf'])
            ->name('pdf')
            ->where('type', 'appointments|services|orders');

        // Preview PDF (for testing)
        Route::get('preview/{type}', function ($type) {
            return app(ExportController::class)->exportPdf(request(), $type);
        })->name('preview')->where('type', 'appointments|services|orders');
    });

        Route::resource('promotions', PromotionController::class);

        // Additional promotion routes
        Route::post('promotions/{promotion}/apply', [PromotionController::class, 'applyCode'])
            ->name('promotions.apply');

        // Promotion usage report
        Route::get('promotions/{promotion}/usage', [PromotionController::class, 'usageReport'])
            ->name('promotions.usage');
    });
});

// Subscription required page
Route::get('/subscription-required', [\App\Http\Controllers\SubscriptionController::class, 'showRequired'])
    ->name('subscription.required');

// Auth Routes
Route::namespace('App\Http\Controllers\Auth')->group(function () {
    // Authentication Routes...
    Route::get('login', 'AuthenticatedSessionController@create')->name('login');
    Route::post('login', 'AuthenticatedSessionController@store');
    Route::post('logout', 'AuthenticatedSessionController@destroy')->name('logout');

    // Registration Routes...
    Route::get('register', 'RegisteredUserController@create')->name('register');
    Route::post('register', 'RegisteredUserController@store');

    // Password Reset Routes...
    Route::get('password/reset', 'PasswordResetLinkController@create')->name('password.request');
    Route::post('password/email', 'PasswordResetLinkController@store')->name('password.email');
    Route::get('password/reset/{token}', 'NewPasswordController@create')->name('password.reset');
    Route::post('password/reset', 'NewPasswordController@store')->name('password.update');

    // Email Verification Routes...
    Route::get('email/verify', 'EmailVerificationPromptController@__invoke')
                ->middleware('auth:web')
                ->name('verification.notice');

    Route::get('email/verify/{id}/{hash}', 'VerifyEmailController@__invoke')
                ->middleware(['auth:web', 'signed', 'throttle:6,1'])
                ->name('verification.verify');

    Route::post('email/verification-notification', 'EmailVerificationNotificationController@store')
                ->middleware(['auth:web', 'throttle:6,1'])
                ->name('verification.send');

    // Confirm Password...
    Route::get('confirm-password', 'ConfirmablePasswordController@show')
                ->middleware('auth:web')
                ->name('password.confirm');

    Route::post('confirm-password', 'ConfirmablePasswordController@store')
                ->middleware('auth:web');
});

// Protected Routes
Route::middleware(['auth:web', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Payroll Management Routes
    Route::middleware(['auth:sanctum', 'verified'])->prefix('payroll')->name('payroll.')->group(function () {
        // Employee Management
        Route::get('/employees', [\App\Http\Controllers\PayrollController::class, 'employeeIndex'])->name('employees.index');
        Route::get('/employees/create', [\App\Http\Controllers\PayrollController::class, 'employeeCreate'])->name('employees.create');
        Route::get('/employees/{id}/edit', [\App\Http\Controllers\PayrollController::class, 'employeeEdit'])->name('employees.edit');

        // Time Clock Management
        Route::get('/time-clock', [\App\Http\Controllers\PayrollController::class, 'timeClockIndex'])->name('time-clock.index');
        Route::get('/time-clock/entry', [\App\Http\Controllers\PayrollController::class, 'timeClockEntry'])->name('time-clock.entry');

        // Payroll Records Management
        Route::get('/records', [\App\Http\Controllers\PayrollController::class, 'payrollIndex'])->name('records.index');
        Route::get('/records/generate', [\App\Http\Controllers\PayrollController::class, 'payrollGenerate'])->name('records.generate');
        Route::get('/records/{id}', [\App\Http\Controllers\PayrollController::class, 'payrollShow'])->name('records.show');

        // Payroll Reports
        Route::get('/reports', [\App\Http\Controllers\PayrollController::class, 'payrollReports'])->name('reports.index');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
