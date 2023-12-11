<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'team_id' => 1,
            'pipeline_id' => 1,
            'stage_id' => 1,
            'id' => 83982738127,
            'order_reference' => 'test_order_reference_1',
            'customer_first_name' => 'Jhon Hyttel',
            'customer_last_name' => 'Angel',
            'customer_email' => 'jhon@nordiccharge.com',
            'customer_phone' => '+45 52 43 48 43',
            'shipping_address' => 'Landskronagade 6',
            'postal_id' => 1,
            'city_id' => 1,
            'country_id' => 1,
            'tracking_code' => 1234,
            'wished_installation_date' => '2023-12-23',
            'installation_date' => '2023-12-24'
        ];
    }
}
