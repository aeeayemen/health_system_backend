<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Patient>
 */
class PatientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'fullname' => $this->faker->name,
            'gender' => $this->faker->randomElement(['Male', 'Female']),
            'height' => $this->faker->numberBetween(140, 200),
            'weight' => $this->faker->numberBetween(40, 150),
            'phone_number' => $this->faker->phoneNumber,
            'image' => 'profile.jpg',
            'birthdate' => $this->faker->date('Y-m-d', '2005-01-01'),
            'physical_activity' => $this->faker->randomElement(['Sedentary', 'Moderate', 'Active']),
            'medical' => $this->faker->randomElement(['None', 'Diabetes', 'Hypertension']),
            'target_weight' => $this->faker->numberBetween(40, 100),
            'allergies' => $this->faker->randomElement(['None', 'Peanuts', 'Dairy']),
        ];
    }
}
