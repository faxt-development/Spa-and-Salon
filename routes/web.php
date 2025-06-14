<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Dashboard routes
Route::middleware(['auth'])->group(function () {
    // Common authenticated user routes
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Admin routes
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'admin'])->name('dashboard');
        
        // Appointments routes for admin
        Route::resource('appointments', 'App\Http\Controllers\Admin\AppointmentController')
            ->except(['show']);
    });
    
    // Staff routes
    Route::middleware(['role:staff'])->prefix('staff')->name('staff.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'staff'])->name('dashboard');
        
        // Appointments routes for staff
        Route::resource('appointments', 'App\Http\Controllers\Staff\AppointmentController')
            ->except(['destroy']);
    });
    
    // Common appointments routes for both admin and staff
    Route::resource('appointments', 'App\Http\Controllers\AppointmentController')
        ->middleware(['role:admin|staff'])
        ->only(['index', 'show']);
    
    // Client routes
    Route::middleware(['role:client'])->prefix('client')->name('client.')->group(function () {
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
                ->middleware('auth')
                ->name('verification.notice');

    Route::get('email/verify/{id}/{hash}', 'VerifyEmailController@__invoke')
                ->middleware(['auth', 'signed', 'throttle:6,1'])
                ->name('verification.verify');

    Route::post('email/verification-notification', 'EmailVerificationNotificationController@store')
                ->middleware(['auth', 'throttle:6,1'])
                ->name('verification.send');

    // Confirm Password...
    Route::get('confirm-password', 'ConfirmablePasswordController@show')
                ->middleware('auth')
                ->name('password.confirm');

    Route::post('confirm-password', 'ConfirmablePasswordController@store')
                ->middleware('auth');
});

// Protected Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
