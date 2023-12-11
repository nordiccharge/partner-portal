<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Stage>
 */
class StageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Test Step',
            'pipeline_id' => 1,
            'order' => $this->faker->randomKey([0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10]),
            'state' => 'step',
        ];
    }
}
