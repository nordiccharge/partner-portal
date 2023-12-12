<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sku' => 'nc-0001',
            'name' => $this->faker->name(),
            'brand_id' => 1,
            'category_id' => 1,
            'retail_price' => 0,
            'purchase_price' => 0,
            'is_active' => true,
            'delivery_information' => ''
        ];
    }
}
