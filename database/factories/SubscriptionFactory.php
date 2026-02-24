<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Subscription>
 */
class SubscriptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'plan_type' => $this->faker->randomElement(['Basic', 'Premium', 'Elite']),
            'price' => $this->faker->randomFloat(2, 10, 200),
            'duration_months' => $this->faker->numberBetween(1, 12),
            'start_date' => $this->faker->date(),
            'end_date' => $this->faker->date(),
            'status' => $this->faker->randomElement(['active', 'expired', 'pending']),
            'auto_renew' => $this->faker->boolean,
        ];
    }
}
