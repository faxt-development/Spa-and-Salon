<?php

namespace Database\Factories;

use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoomFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Room::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->unique()->word,
            'description' => $this->faker->sentence,
            'room_number' => (string) $this->faker->unique()->numberBetween(100, 999),
            'floor' => $this->faker->numberBetween(1, 5),
            'capacity' => $this->faker->numberBetween(1, 5),
            'is_active' => true,
            'room_type' => $this->faker->randomElement(['treatment', 'massage', 'facial', 'manicure', 'pedicure']),
            'hourly_rate' => $this->faker->randomFloat(2, 50, 200),
            'daily_rate' => $this->faker->randomFloat(2, 200, 800),
        ];
    }

    /**
     * Indicate that the room is inactive.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function inactive()
    {
        return $this->state([
            'is_active' => false,
        ]);
    }
}
