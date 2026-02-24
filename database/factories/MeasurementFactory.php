<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Measurement>
 */
class MeasurementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'measurement_date' => $this->faker->date(),
            'weight' => $this->faker->randomFloat(1, 40, 150),
            'body_fat_percentage' => $this->faker->randomFloat(1, 5, 50),
            'muscle_mass' => $this->faker->randomFloat(1, 20, 80),
            'waist_circumference' => $this->faker->randomFloat(1, 60, 120),
            'hip_circumference' => $this->faker->randomFloat(1, 70, 130),
        ];
    }
}
