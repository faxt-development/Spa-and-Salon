<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\StaffController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Auth routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Auth routes that require authentication
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
});

// Public routes
Route::get('/services', [ServiceController::class, 'index']);
Route::get('/services/{service}', [ServiceController::class, 'show']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Appointments
    Route::apiResource('appointments', AppointmentController::class);
    Route::get('/appointments/calendar/{year}/{month}', [AppointmentController::class, 'calendar']);
    Route::post('/appointments/{appointment}/cancel', [AppointmentController::class, 'cancel']);
    Route::post('/appointments/{appointment}/complete', [AppointmentController::class, 'complete']);
    
    // Booking
    Route::get('/booking/availability', [BookingController::class, 'checkAvailability']);
    Route::post('/booking/reserve', [BookingController::class, 'reserve']);
    
    // Clients
    Route::apiResource('clients', ClientController::class);
    Route::get('/clients/{client}/appointments', [ClientController::class, 'appointments']);
    
    // Staff
    Route::apiResource('staff', StaffController::class);
    Route::get('/staff/{staff}/schedule', [StaffController::class, 'schedule']);
    Route::get('/staff/{staff}/appointments', [StaffController::class, 'appointments']);
    
    // Services
    Route::post('/services', [ServiceController::class, 'store']);
    Route::put('/services/{service}', [ServiceController::class, 'update']);
    Route::delete('/services/{service}', [ServiceController::class, 'destroy']);
    
    // Payments
    Route::apiResource('payments', PaymentController::class);
    
    // Products
    Route::apiResource('products', ProductController::class);
});
