<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Doctor>
 */
class DoctorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'gender' => $this->faker->randomElement(['Male', 'Female']),
            'degree' => $this->faker->randomElement(['PhD in Nutrition', 'MD Cardiology', 'Master of Public Health']),
            'bank_account' => $this->faker->bankAccountNumber,
            'phone_number' => $this->faker->phoneNumber,
            'CV' => 'path/to/cv.pdf',
            'application_status' => $this->faker->randomElement(['pending', 'approved', 'rejected']),
            'specialization' => $this->faker->randomElement(['Nutritionist', 'Cardiologist', 'Dermatologist']),
            'license_number' => $this->faker->bothify('LIC-#####'),
            'years_of_experience' => $this->faker->numberBetween(1, 40),
            'bio' => $this->faker->paragraph,
            'profile_image' => 'profile.jpg',
            'is_verified' => $this->faker->boolean,
            'rating' => $this->faker->randomFloat(1, 1, 5),
            'consultation_fee' => $this->faker->randomFloat(2, 50, 500),
            'is_available' => $this->faker->boolean,
        ];
    }
}
