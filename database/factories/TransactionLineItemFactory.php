<?php

namespace Database\Factories;

use App\Models\Staff;
use App\Models\Transaction;
use App\Models\TransactionLineItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionLineItemFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TransactionLineItem::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'transaction_id' => Transaction::factory(),
            'staff_id' => Staff::factory(),
            'item_type' => $this->faker->randomElement(['service', 'product', 'tax', 'discount', 'tip']),
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence,
            'quantity' => $this->faker->numberBetween(1, 5),
            'unit_price' => $this->faker->randomFloat(2, 10, 200),
            'tax_rate' => $this->faker->randomFloat(2, 0, 20),
            'amount' => function (array $attributes) {
                return $attributes['quantity'] * $attributes['unit_price'] * (1 + ($attributes['tax_rate'] / 100));
            },
            'metadata' => null,
        ];
    }

    /**
     * Configure the model factory to create a service line item.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function service()
    {
        return $this->state([
            'item_type' => 'service',
            'name' => $this->faker->randomElement(['Haircut', 'Massage', 'Facial', 'Manicure', 'Pedicure']),
        ]);
    }

    /**
     * Configure the model factory to create a product line item.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function product()
    {
        return $this->state([
            'item_type' => 'product',
            'name' => $this->faker->randomElement(['Shampoo', 'Conditioner', 'Hair Spray', 'Nail Polish']),
        ]);
    }

    /**
     * Configure the model factory to create a tax line item.
     *
     * @param  float  $rate
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function tax($rate = null)
    {
        return $this->state([
            'item_type' => 'tax',
            'name' => 'Sales Tax',
            'tax_rate' => $rate ?? $this->faker->randomFloat(2, 5, 15),
            'unit_price' => 0,
            'quantity' => 1,
            'amount' => function (array $attributes) {
                // This will be calculated based on the parent transaction's subtotal
                return 0;
            },
        ]);
    }

    /**
     * Configure the model factory to create a tip line item.
     *
     * @param  float  $amount
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function tip($amount = null)
    {
        return $this->state([
            'item_type' => 'tip',
            'name' => 'Gratuity',
            'unit_price' => $amount ?? $this->faker->randomFloat(2, 5, 50),
            'quantity' => 1,
            'tax_rate' => 0,
            'amount' => function (array $attributes) {
                return $attributes['unit_price'];
            },
        ]);
    }
}
