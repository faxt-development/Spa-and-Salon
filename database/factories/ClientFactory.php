<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function definition()
    {
        return [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => $this->faker->unique()->safeEmail,
            'phone' => $this->faker->phoneNumber,
            'address' => $this->faker->address,
            'date_of_birth' => $this->faker->date(),
            'notes' => $this->faker->paragraph,
            'marketing_consent' => $this->faker->boolean,
            'source' => $this->faker->randomElement(['walk_in', 'website', 'referral', 'social_media']),
            'total_spent' => $this->faker->randomFloat(2, 0, 10000),
            'visit_count' => $this->faker->numberBetween(0, 100),
            'first_visit_at' => $this->faker->dateTimeThisYear,
            'last_visit' => $this->faker->dateTimeThisMonth,
        ];
    }
}
