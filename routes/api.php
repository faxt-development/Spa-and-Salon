<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\WalkInController;
use App\Http\Controllers\Api\StaffController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PayrollController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\TaxController;
use App\Http\Controllers\Api\TransactionController;

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
Route::get('/tax-rates', [TaxController::class, 'index']);
Route::post('/tax/calculate', [TaxController::class, 'calculate']);

// Public services endpoints
Route::get('/services', [ServiceController::class, 'index']);
Route::get('/services/{service}', [ServiceController::class, 'show']);
Route::get('/public/services', [ServiceController::class, 'publicServices']);
Route::get('/public/services/categories', [ServiceController::class, 'publicCategories']);
Route::get('/public/services/category/{category}', [ServiceController::class, 'servicesByCategory']);
Route::post('/booking/availability', [BookingController::class, 'checkAvailability']);

// Client appointments
Route::middleware('auth:sanctum')->get('/client/appointments', [AppointmentController::class, 'clientAppointments']);

// Gift Card Routes (public for checking balance, protected for management)
Route::get('/gift-cards/check-balance/{code}', [\App\Http\Controllers\Api\GiftCardController::class, 'checkBalance']);
Route::get('/gift-cards/{code}', [\App\Http\Controllers\Api\GiftCardController::class, 'show']);
Route::post('/gift-cards/payment-intent', [\App\Http\Controllers\Api\GiftCardController::class, 'createPaymentIntent'])->name('gift-cards.create-payment-intent');
Route::post('/gift-cards/handle-payment', [\App\Http\Controllers\Api\GiftCardController::class, 'handleSuccessfulPayment'])->name('gift-cards.handle-payment');

// Payment-related routes
Route::post('/gift-cards/create-payment-intent', [\App\Http\Controllers\Api\GiftCardController::class, 'createPaymentIntent']);
Route::post('/gift-cards/confirm-payment', [\App\Http\Controllers\Api\GiftCardController::class, 'handleSuccessfulPayment']);

// Stripe webhook (must be outside auth middleware)
Route::post('/stripe/webhook', [\App\Http\Controllers\Api\StripeWebhookController::class, 'handleWebhook'])
    ->name('stripe.webhook');

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Dashboard statistics
    Route::get('/dashboard/appointments/stats', [AppointmentController::class, 'getTodaysAppointmentStats']);
    Route::get('/dashboard/staff/stats', [DashboardController::class, 'getStaffStats']);
    Route::get('/dashboard/revenue/stats', [DashboardController::class, 'getRevenueStats']);
    Route::get('/dashboard/walk-ins/queue-stats', [WalkInController::class, 'getQueueStats']);

    // Gift Card Management
    Route::apiResource('gift-cards', \App\Http\Controllers\Api\GiftCardController::class)->except(['show', 'store']);
    Route::post('/gift-cards/{code}/redeem', [\App\Http\Controllers\Api\GiftCardController::class, 'redeem']);
    Route::post('/gift-cards/{id}/deactivate', [\App\Http\Controllers\Api\GiftCardController::class, 'deactivate']);
    // Tax routes
    Route::get('/orders/{order}/tax-breakdown', [TaxController::class, 'orderBreakdown']);
    // Appointments
    Route::apiResource('appointments', AppointmentController::class);

    Route::get('/appointments/calendar/{year}/{month}', [AppointmentController::class, 'calendar']);
    Route::post('/appointments/{appointment}/cancel', [AppointmentController::class, 'cancel']);
    Route::post('/appointments/{appointment}/complete', [AppointmentController::class, 'complete']);

    // Booking
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
    Route::post('/products/{product}/inventory', [ProductController::class, 'updateInventory']);

    // Product Categories
    Route::apiResource('product-categories', \App\Http\Controllers\Api\ProductCategoryController::class);
    Route::get('/product-categories/hierarchy', [\App\Http\Controllers\Api\ProductCategoryController::class, 'hierarchy']);

    // Suppliers
    Route::apiResource('suppliers', \App\Http\Controllers\Api\SupplierController::class);

    // Orders
    Route::apiResource('orders', \App\Http\Controllers\Api\OrderController::class);

    // Payments
    Route::apiResource('payments', \App\Http\Controllers\Api\PaymentController::class);
    Route::post('/payments/{id}/refund', [\App\Http\Controllers\Api\PaymentController::class, 'refund']);
    
    // Transactions
    Route::apiResource('transactions', \App\Http\Controllers\Api\TransactionController::class);
    Route::post('/transactions/{id}/process-payment', [\App\Http\Controllers\Api\TransactionController::class, 'processPayment']);
    Route::post('/transactions/{id}/process-refund', [\App\Http\Controllers\Api\TransactionController::class, 'processRefund']);

    // Inventory Transactions
    Route::apiResource('inventory-transactions', \App\Http\Controllers\Api\InventoryTransactionController::class, ['only' => ['index', 'show']]);
    Route::get('/inventory-transactions/product/{productId}', [\App\Http\Controllers\Api\InventoryTransactionController::class, 'productHistory']);
    Route::get('/inventory-transactions/summary', [\App\Http\Controllers\Api\InventoryTransactionController::class, 'summary']);

    // Employees
    Route::apiResource('employees', \App\Http\Controllers\Api\EmployeeController::class);

    // Payroll
    Route::apiResource('payroll', \App\Http\Controllers\Api\PayrollController::class);
    Route::post('/payroll/{id}/process', [\App\Http\Controllers\Api\PayrollController::class, 'process']);
    Route::post('/payroll/{id}/cancel', [\App\Http\Controllers\Api\PayrollController::class, 'cancel']);
    Route::post('/payroll/generate', [\App\Http\Controllers\Api\PayrollController::class, 'generatePayroll']);

    // Time Clock
    Route::apiResource('time-clock', \App\Http\Controllers\Api\TimeClockController::class, ['only' => ['index', 'update']]);
    Route::post('/time-clock/clock-in', [\App\Http\Controllers\Api\TimeClockController::class, 'clockIn']);
    Route::post('/time-clock/clock-out', [\App\Http\Controllers\Api\TimeClockController::class, 'clockOut']);
    Route::post('/time-clock/{id}/approve', [\App\Http\Controllers\Api\TimeClockController::class, 'approve']);
    Route::get('/time-clock/employee/{employeeId}/status', [\App\Http\Controllers\Api\TimeClockController::class, 'status']);
    Route::get('/time-clock/employee/{employeeId}/weekly-report', [\App\Http\Controllers\Api\TimeClockController::class, 'weeklyReport']);

    // Loyalty Program
    Route::prefix('loyalty')->group(function () {
        Route::get('/account', [\App\Http\Controllers\Api\LoyaltyController::class, 'getAccount']);
        Route::post('/orders/{order}/redeem', [\App\Http\Controllers\Api\LoyaltyController::class, 'redeemPoints']);
        Route::get('/orders/{order}/promotions', [\App\Http\Controllers\Api\LoyaltyController::class, 'getEligiblePromotions']);
    });

    // Staff Performance Metrics
    Route::prefix('staff-performance')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\StaffPerformanceController::class, 'index']);
        Route::get('/utilization', [\App\Http\Controllers\Api\StaffPerformanceController::class, 'utilizationReport']);
        Route::get('/revenue', [\App\Http\Controllers\Api\StaffPerformanceController::class, 'revenueReport']);
        Route::get('/commissions', [\App\Http\Controllers\Api\StaffPerformanceController::class, 'commissionReport']);
        Route::get('/staff/{staff}/summary', [\App\Http\Controllers\Api\StaffPerformanceController::class, 'staffSummary']);
    });

    // Commission Structures
    Route::apiResource('commission-structures', \App\Http\Controllers\Api\CommissionStructureController::class)
        ->except(['edit', 'create']);
        
    // Commission Payments
    Route::apiResource('commission-payments', \App\Http\Controllers\Api\CommissionPaymentController::class)
        ->except(['edit', 'create']);
    Route::get('commission-payments/summary', [\App\Http\Controllers\Api\CommissionPaymentController::class, 'summary']);
    Route::get('commission-payments/{commission_payment}/metrics', [\App\Http\Controllers\Api\CommissionPaymentController::class, 'metrics']);
    Route::get('staff/{staff}/commission-payments', [\App\Http\Controllers\Api\CommissionPaymentController::class, 'staffHistory']);

    // Tax Reports
    Route::prefix('reports')->group(function () {
        Route::get('/tax/summary', [ReportController::class, 'taxSummary']);
        Route::get('/tax/detailed', [ReportController::class, 'taxDetailed']);
    });
});
