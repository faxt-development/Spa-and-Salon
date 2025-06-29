<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition()
    {
        return [
            'client_id' => \App\Models\Client::factory(),
            'status' => 'completed',
            'total_amount' => $this->faker->randomFloat(2, 10, 1000),
            // Add any other required fields from your orders table
        ];
    }
}
