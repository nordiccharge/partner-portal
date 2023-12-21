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
            'company_type_id' => 1,
            'name' => 'Test Company',
            'sender_name' => 'Test Company ApS',
            'sender_address' => 'Addresse 12',
            'sender_zip' => '1234',
            'sender_city' => 'Herlev',
            'sender_country' => 'DK',
        ];
    }
}
