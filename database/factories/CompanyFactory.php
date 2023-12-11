<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Company>
 */
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
            'company_type_id' => 2,
            'name' => $this->faker->company(),
            'description' => $this->faker->realText(),
            'sender_name' => $this->faker->company(),
            'sender_attention' => '',
            'sender_address' => $this->faker->address(),
            'sender_address2' => '',
            'sender_zip' => $this->faker->postcode(),
            'sender_city' => $this->faker->city(),
            'sender_country' => $this->faker->country(),
            'sender_state' => '',
            'sender_phone' => $this->faker->phoneNumber(),
            'sender_email' => $this->faker->companyEmail()
        ];
    }
}
