<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Models\Company;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\DB;

// Bootstrap Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Get a company
$company = Company::first();
if (!$company) {
    echo "No companies found. Please run the seeder first.\n";
    exit(1);
}

echo "Company: {$company->name} (ID: {$company->id})\n\n";

// Test the paymentMethods() relationship
try {
    $paymentMethods = $company->paymentMethods()->get();
    echo "Payment methods count: " . $paymentMethods->count() . "\n";
    
    if ($paymentMethods->count() > 0) {
        echo "Payment methods for this company:\n";
        foreach ($paymentMethods as $method) {
            echo "- {$method->display_name} ({$method->name})\n";
        }
    } else {
        echo "No payment methods found for this company.\n";
    }
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " (Line: " . $e->getLine() . ")\n";
}

// Check if there are any payment methods in the database
$allPaymentMethods = PaymentMethod::all();
echo "\nTotal payment methods in database: " . $allPaymentMethods->count() . "\n";

// Check company_payment_method records
$pivotCount = DB::table('company_payment_method')
    ->where('company_id', $company->id)
    ->count();
echo "Payment methods associated with this company in pivot table: " . $pivotCount . "\n";

// Check if the middleware is working now
echo "\nTesting CheckOnboardingStatus middleware logic:\n";
if ($company && $company->paymentMethods()->count() > 0) {
    echo "✓ Company has payment methods - onboarding checklist item would be marked as completed\n";
} else {
    echo "✗ Company has no payment methods - onboarding checklist item would not be marked as completed\n";
}
