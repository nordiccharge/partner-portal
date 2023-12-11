<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Installer>
 */
class InstallerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => 1,
            'contact_email' => 'milas@gsr-teknik.dk',
            'contact_phone' => '+45 53 69 92 93',
            'invoice_email' => 'faktura@gsr-teknik.dk'
        ];
    }
}
