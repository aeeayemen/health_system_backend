<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'invoice_number' => 'INV-' . strtoupper($this->faker->unique()->bothify('??####')),
            'total_amount' => $this->faker->randomFloat(2, 50, 500),
            'amount' => $this->faker->randomFloat(2, 50, 500),
            'payment_status' => $this->faker->randomElement(['paid', 'unpaid', 'partially_paid']),
            'due_date' => $this->faker->dateTimeBetween('now', '+1 month'),
        ];
    }
}
