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
            'name' => 'Bak Studio',
            'description' => '',
            'sender_name' => 'Bak Studio',
            'sender_attention' => 'Sebastian Bak Lundahl',
            'sender_address' => 'Teglgårdstræde 4b',
            'sender_address2' => '',
            'sender_zip' => '1452',
            'sender_city' => 'København K',
            'sender_country' => 'DK',
            'sender_state' => '',
            'sender_phone' => '+45 22 22 93 70',
            'sender_email' => 'sbl@bakstudio.dk'
        ];
    }
}
