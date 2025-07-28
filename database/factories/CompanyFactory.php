<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CompanyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'theme_id' => null,
            'name' => $this->faker->company,
            'address' => $this->faker->address,
            'city' => $this->faker->city,
            'state' => $this->faker->stateAbbr,
            'zip' => $this->faker->postcode,
            'phone' => $this->faker->phoneNumber,
            'website' => $this->faker->url,
            'domain' => $this->faker->domainName,
            'is_primary_domain' => false,
            'homepage_content' => null,
            'theme_settings' => null,
            'logo' => null,
            'description' => $this->faker->sentence,
        ];
    }
}
