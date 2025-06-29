<?php

namespace Database\Factories;

use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition()
    {
        return [
            'amount' => $this->faker->randomFloat(2, 10, 1000),
            'status' => 'completed',
            'payment_method' => $this->faker->randomElement(['credit_card', 'debit_card', 'paypal']),
            'transaction_id' => $this->faker->uuid,
            'created_at' => now(),
            'updated_at' => now(),
            // Add any other required fields from your payments table
        ];
    }
}
