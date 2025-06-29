<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Room;
use App\Models\Staff;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Transaction::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'client_id' => Client::factory(),
            'staff_id' => Staff::factory(),
            'room_id' => Room::factory(),
            'payment_method' => $this->faker->randomElement(['credit_card', 'debit_card', 'cash', 'gift_card']),
            'transaction_type' => $this->faker->randomElement([
                'appointment', 'retail', 'gift_card', 'refund', 'other'
            ]),
            'reference_type' => null,
            'reference_id' => null,
            'subtotal' => $this->faker->randomFloat(2, 10, 1000),
            'tax_amount' => $this->faker->randomFloat(2, 1, 200),
            'tip_amount' => $this->faker->randomFloat(2, 0, 100),
            'tip_distribution_method' => null,
            'tips_distributed' => false,
            'tips_distributed_at' => null,
            'discount_amount' => 0,
            'total_amount' => function (array $attributes) {
                return $attributes['subtotal'] + $attributes['tax_amount'] + $attributes['tip_amount'] - $attributes['discount_amount'];
            },
            'status' => 'completed',
            'transaction_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'payment_gateway' => $this->faker->randomElement(['stripe', 'paypal', 'square', 'other']),
            'card_last_four' => $this->faker->randomNumber(4, true),
            'card_brand' => $this->faker->creditCardType,
            'external_transaction_id' => $this->faker->uuid,
            'parent_transaction_id' => null,
        ];
    }

    /**
     * Configure the model factory to create a transaction with a tip.
     *
     * @param  float  $amount
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function withTip(float $amount)
    {
        return $this->state([
            'tip_amount' => $amount,
        ]);
    }

    /**
     * Configure the model factory to create a refund transaction.
     *
     * @param  int  $parentTransactionId
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function refund($parentTransactionId)
    {
        return $this->state([
            'transaction_type' => 'refund',
            'parent_transaction_id' => $parentTransactionId,
            'status' => 'completed',
        ]);
    }

    /**
     * Configure the model factory to create a pending transaction.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function pending()
    {
        return $this->state([
            'status' => 'pending',
        ]);
    }

    /**
     * Configure the model factory to create a failed transaction.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function failed()
    {
        return $this->state([
            'status' => 'failed',
        ]);
    }
}
