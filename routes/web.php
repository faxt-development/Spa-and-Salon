<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\InventoryController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Debug route to check authentication and token status
Route::middleware(['auth'])->get('/debug-auth', function () {
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
Route::middleware(['auth'])->group(function () {
    // Common authenticated user routes
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Admin routes
    Route::middleware(['auth:web', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'admin'])->name('dashboard');
        Route::resource('clients', 'App\Http\Controllers\Admin\ClientController');
        
        // Category reordering API routes
        Route::post('/categories/reorder', [\App\Http\Controllers\Inventory\CategoryController::class, 'reorder'])
            ->name('categories.reorder');
        Route::get('/categories/tree', [\App\Http\Controllers\Inventory\CategoryController::class, 'tree'])
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
    });

        // POS Routes
    Route::prefix('pos')->name('pos.')->group(function () {
        Route::get('/', [PosController::class, 'index'])->name('index');
        Route::get('/receipt/{order}', [PosController::class, 'receipt'])->name('receipt');
        Route::get('/receipt/{order}/print', [PosController::class, 'printReceipt'])->name('receipt.print');
        
        // API endpoints for POS
        Route::get('/products', [PosController::class, 'getProducts']);
        Route::get('/services', [PosController::class, 'getServices']);
        Route::get('/customers', [PosController::class, 'getCustomers']);
        Route::post('/process-payment', [PosController::class, 'processPayment'])->name('process-payment');
    });

    // Staff routes
    Route::middleware(['auth', 'role:staff'])->prefix('staff')->name('staff.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'staff'])->name('dashboard');
    });

    // Inventory Management Routes
    Route::middleware(['auth', 'role:admin|staff'])->prefix('inventory')->name('inventory.')->group(function () {
        // Main inventory dashboard
        Route::get('/', [InventoryController::class, 'index'])->name('index');
        
        // Product resource routes
        Route::resource('products', InventoryController::class)->except(['index', 'show']);
        
        // Product details and actions
        Route::get('products/{product}/details', [InventoryController::class, 'show'])->name('products.details');
        Route::post('products/{product}/adjust', [InventoryController::class, 'updateInventory'])->name('products.adjust');
        
        // Inventory reports
        Route::get('reports/low-stock', [InventoryController::class, 'lowStockReport'])->name('reports.low-stock');
        
        // Bulk actions
        Route::post('bulk-actions', [InventoryController::class, 'bulkActions'])->name('bulk-actions');
        
        // Categories management
        Route::resource('categories', 'Inventory\CategoryController')->except(['show']);
        
        // Bulk actions for categories
        Route::post('categories/bulk', [\App\Http\Controllers\Inventory\CategoryController::class, 'bulkActions'])
            ->name('categories.bulk');
        
        // API endpoint to get product count for a category
        Route::get('api/categories/{category}/products/count', [\App\Http\Controllers\Inventory\CategoryController::class, 'getProductCount'])
            ->name('categories.products.count');
        
        // Suppliers management
        Route::resource('suppliers', 'App\Http\Controllers\SupplierController')->except(['show']);
    });

    // Appointments routes for both admin and staff
    Route::resource('appointments', 'App\Http\Controllers\AppointmentController')
        ->middleware(['auth:web', 'role:admin|staff'])
        ->except(['destroy']);

    // Client routes
    Route::middleware(['auth:web', 'role:client'])->prefix('client')->name('client.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'client'])->name('dashboard');
        // Add more client routes here
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
