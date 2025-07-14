<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CompanyUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * This seeder migrates existing company-user relationships to the new many-to-many pivot table.
     */
    public function run(): void
    {
        // Get all companies
        $companies = Company::all();
        $count = 0;
        
        foreach ($companies as $company) {
            // Find the user who owns this company (based on user_id in companies table)
            if ($company->user_id) {
                $user = User::find($company->user_id);
                
                if ($user) {
                    // Check if relationship already exists
                    $exists = DB::table('company_user')
                        ->where('company_id', $company->id)
                        ->where('user_id', $user->id)
                        ->exists();
                    
                    if (!$exists) {
                        // Create the relationship in the pivot table
                        DB::table('company_user')->insert([
                            'company_id' => $company->id,
                            'user_id' => $user->id,
                            'is_primary' => true,
                            'role' => 'admin',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        
                        $count++;
                        $this->command->info("Associated user {$user->id} with company {$company->id}");
                    }
                }
            }
        }
        
        $this->command->info("Migration complete. Created {$count} company-user relationships.");
    }
}
