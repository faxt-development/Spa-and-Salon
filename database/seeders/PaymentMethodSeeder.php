<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define common payment methods
        $paymentMethods = [
            [
                'name' => 'credit_card',
                'display_name' => 'Credit Card',
                'description' => 'Pay with Visa, Mastercard, American Express, or Discover',
                'is_active' => true,
                'requires_card_details' => true,
                'icon' => 'credit-card',
                'display_order' => 1,
            ],
            [
                'name' => 'cash',
                'display_name' => 'Cash',
                'description' => 'Pay with cash',
                'is_active' => true,
                'requires_card_details' => false,
                'icon' => 'money-bill',
                'display_order' => 2,
            ],
            [
                'name' => 'gift_card',
                'display_name' => 'Gift Card',
                'description' => 'Pay with a gift card',
                'is_active' => true,
                'requires_card_details' => false,
                'icon' => 'gift',
                'display_order' => 3,
            ],
            [
                'name' => 'mobile_payment',
                'display_name' => 'Mobile Payment',
                'description' => 'Pay with Apple Pay, Google Pay, or Samsung Pay',
                'is_active' => true,
                'requires_card_details' => false,
                'icon' => 'mobile-alt',
                'display_order' => 4,
            ],
        ];

        // Create payment methods
        foreach ($paymentMethods as $method) {
            PaymentMethod::updateOrCreate(
                ['name' => $method['name']],
                $method
            );
        }

        $this->command->info('Payment methods seeded successfully.');
    }
}
