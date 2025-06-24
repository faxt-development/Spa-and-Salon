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


// Public routes
Route::get('/', function () {
    return view('welcome');
})->name('home');


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
Route::middleware(['auth:web'])->group(function () {
    // Common authenticated user routes
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Gift card history for authenticated users
    Route::get('/gift-cards/history-user', [GiftCardWebController::class, 'history'])
        ->name('gift-cards.history-user');

    // Admin routes
    Route::middleware(['auth:web', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'admin'])->name('dashboard');
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

    // Staff routes
    Route::middleware(['auth:web', 'role:staff'])->prefix('staff')->name('staff.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'staff'])->name('dashboard');
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
  

    // Client routes
    Route::middleware(['auth:web', 'role:admin|staff'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'client'])->name('dashboard');
        // Add more client routes here
    });

    Route::middleware(['auth:web', 'role:admin'])->group(function () {

    // Email Marketing Dashboard
    Route::get('/email-marketing/dashboard', [EmailMarketingDashboardController::class, 'index'])->name('email-marketing.dashboard');

    // Drip Campaign Routes
    Route::resource('drip-campaigns', DripCampaignController::class);


        Route::resource('promotions', PromotionController::class);

        // Additional promotion routes
        Route::post('promotions/{promotion}/apply', [PromotionController::class, 'applyCode'])
            ->name('promotions.apply');

        // Promotion usage report
        Route::get('promotions/{promotion}/usage', [PromotionController::class, 'usageReport'])
            ->name('promotions.usage');
    });
});

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
