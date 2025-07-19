<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\PaymentMethod;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CompanyPaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all companies and payment methods
        $companies = Company::all();
        $paymentMethods = PaymentMethod::all();
        
        if ($companies->isEmpty()) {
            $this->command->info('No companies found. Please run the company seeder first.');
            return;
        }
        
        if ($paymentMethods->isEmpty()) {
            $this->command->info('No payment methods found. Please run the PaymentMethodSeeder first.');
            return;
        }
        
        // Associate all payment methods with all companies
        foreach ($companies as $company) {
            $this->command->info("Associating payment methods with company: {$company->name}");
            
            // Attach all payment methods to the company
            foreach ($paymentMethods as $paymentMethod) {
                $company->paymentMethods()->syncWithoutDetaching([
                    $paymentMethod->id => ['is_active' => true]
                ]);
            }
        }
        
        $this->command->info('Payment methods associated with companies successfully.');
    }
}
