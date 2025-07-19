<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Models\Company;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Support\Facades\DB;

// Bootstrap Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Test the staff relationship
echo "Testing Company->staff() relationship...\n";

// Get a company
$company = Company::first();
if (!$company) {
    echo "No companies found. Please run the seeder first.\n";
    exit(1);
}

echo "Company: {$company->name} (ID: {$company->id})\n";

// Get users with staff role for this company
$staffUsers = DB::table('company_user')
    ->where('company_id', $company->id)
    ->where('role', 'staff')
    ->join('users', 'users.id', '=', 'company_user.user_id')
    ->select('users.*')
    ->get();

echo "Users with staff role in company_user: " . $staffUsers->count() . "\n";
foreach ($staffUsers as $user) {
    echo "- User ID: {$user->id}, Name: {$user->name}, Email: {$user->email}\n";
}

// Get staff records linked to these users
$staffRecords = Staff::whereIn('user_id', $staffUsers->pluck('id'))->get();
echo "\nStaff records linked to these users: " . $staffRecords->count() . "\n";
foreach ($staffRecords as $staff) {
    echo "- Staff ID: {$staff->id}, Name: {$staff->first_name} {$staff->last_name}, User ID: {$staff->user_id}\n";
}

// Test the new relationship
echo "\nTesting the new staff() relationship:\n";
try {
    $companyStaff = $company->staff()->get();
    echo "Staff found through relationship: " . $companyStaff->count() . "\n";
    foreach ($companyStaff as $staff) {
        echo "- Staff ID: {$staff->id}, Name: {$staff->first_name} {$staff->last_name}, User ID: {$staff->user_id}\n";
    }

    // Compare results
    $expectedIds = $staffRecords->pluck('id')->toArray();
    $actualIds = $companyStaff->pluck('id')->toArray();
    $missing = array_diff($expectedIds, $actualIds);
    $extra = array_diff($actualIds, $expectedIds);

    echo "\nComparison:\n";
    echo "- Expected IDs: " . implode(', ', $expectedIds) . "\n";
    echo "- Actual IDs: " . implode(', ', $actualIds) . "\n";
    echo "- Missing IDs: " . implode(', ', $missing) . "\n";
    echo "- Extra IDs: " . implode(', ', $extra) . "\n";

    if (empty($missing) && empty($extra)) {
        echo "\nSUCCESS: The staff() relationship returns the expected results.\n";
    } else {
        echo "\nFAILURE: The staff() relationship does not return the expected results.\n";
    }
} catch (\Exception $e) {
    echo "\nERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " (Line: " . $e->getLine() . ")\n";
}

// Check if there are any staff records at all
echo "\nChecking if there are any staff records in the database:\n";
$allStaff = Staff::all();
echo "Total staff records: " . $allStaff->count() . "\n";
if ($allStaff->count() > 0) {
    echo "Sample staff record:\n";
    $sample = $allStaff->first();
    echo "- ID: {$sample->id}, Name: {$sample->first_name} {$sample->last_name}, User ID: {$sample->user_id}\n";
}

// Check if there are any users with staff role
echo "\nChecking if there are any users with staff role:\n";
$staffRoleUsers = DB::table('company_user')->where('role', 'staff')->count();
echo "Users with staff role: {$staffRoleUsers}\n";
