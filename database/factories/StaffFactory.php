<?php

namespace Database\Factories;

use App\Models\Staff;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StaffFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Staff::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => $this->faker->unique()->safeEmail,
            'phone' => $this->faker->phoneNumber,
            'position' => $this->faker->jobTitle,
            'active' => true,
            'work_start_time' => '09:00:00',
            'work_end_time' => '17:00:00',
            'work_days' => json_encode(['monday', 'tuesday', 'wednesday', 'thursday', 'friday']),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
