<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'transaction_id' => 'TXN-' . strtoupper($this->faker->unique()->bothify('??####')),
            'amount' => $this->faker->randomFloat(2, 10, 500),
            'currency' => 'USD',
            'status' => $this->faker->randomElement(['completed', 'pending', 'failed']),
            'payment_date' => $this->faker->dateTimeThisYear(),
        ];
    }
}
