<?php

namespace Database\Factories;

use App\Models\Postal;
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
        $postal = Postal::where('postal', '2730')->firstOrFail();
        return [
            'id' => 1234,
            'shipping_address' => 'Address 123',
            'team_id' => 1,
            'pipeline_id' => 3,
            'installation_required' => 1,
            'installation_id' => 1,
            'note' => 'Factory created',
            'country_id' => 1,
            'customer_email' => 'test@test.com',
            'installation_date' => now()->addDays(5),
            'customer_first_name' => 'Jhon',
            'customer_last_name' => 'Angel',
            'customer_phone' => '+4512345678',
            'order_reference' => '123456',
            'postal_id' => $postal->id,
            'city_id' => $postal->city->id,
            'created_at' => now(),
        ];
    }
}
