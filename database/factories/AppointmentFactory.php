<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Client;
use App\Models\Staff;
use Illuminate\Database\Eloquent\Factories\Factory;

class AppointmentFactory extends Factory
{
    protected $model = Appointment::class;

    public function definition()
    {
        return [
            'client_id' => Client::factory(),
            'staff_id' => Staff::factory(),
            'status' => 'completed',
            'start_time' => now()->subDays(30),
            'end_time' => now()->subDays(30)->addHour(),
            'total_price' => $this->faker->randomFloat(2, 50, 500),
            'is_paid' => true,
          ];
    }
}
