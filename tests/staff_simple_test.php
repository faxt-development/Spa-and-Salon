<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Models\Company;
use App\Models\Staff;
use App\Models\User;
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

// Test the staff() relationship
try {
    $staff = $company->staff()->get();
    echo "Staff count: " . $staff->count() . "\n";
    
    if ($staff->count() > 0) {
        echo "First staff member: " . $staff->first()->first_name . " " . $staff->first()->last_name . "\n";
    } else {
        echo "No staff members found for this company.\n";
    }
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " (Line: " . $e->getLine() . ")\n";
}

// Check if there are any staff records
$allStaff = Staff::all();
echo "\nTotal staff records in database: " . $allStaff->count() . "\n";

// Check company_user records with staff role
$staffRoleCount = DB::table('company_user')
    ->where('company_id', $company->id)
    ->where('role', 'staff')
    ->count();
echo "Users with 'staff' role for this company: " . $staffRoleCount . "\n";

// Show staff users
$staffUsers = DB::table('company_user')
    ->where('company_id', $company->id)
    ->where('role', 'staff')
    ->join('users', 'users.id', '=', 'company_user.user_id')
    ->select('users.id', 'users.name', 'users.email')
    ->get();

echo "\nUsers with 'staff' role:\n";
foreach ($staffUsers as $user) {
    echo "- User ID: {$user->id}, Name: {$user->name}, Email: {$user->email}\n";
    
    // Check if this user has a staff record
    $staffRecord = Staff::where('user_id', $user->id)->first();
    if ($staffRecord) {
        echo "  Has staff record: Yes (ID: {$staffRecord->id})\n";
    } else {
        echo "  Has staff record: No\n";
    }
}
