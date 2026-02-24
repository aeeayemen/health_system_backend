<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Meal>
 */
class MealFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word,
            'serving' => $this->faker->randomElement(['1 bowl', '1 plate', '200g', '1 cup']),
            'describtion' => $this->faker->sentence,
            'carbo' => $this->faker->numberBetween(0, 100),
            'protin' => $this->faker->numberBetween(0, 50),
            'fat' => $this->faker->numberBetween(0, 50),
            'energy' => $this->faker->numberBetween(50, 800),
            'category' => $this->faker->randomElement(['Breakfast', 'Lunch', 'Dinner', 'Snack']),
            'day_number' => $this->faker->numberBetween(1, 7),
            'meal_type' => $this->faker->randomElement(['main', 'side', 'drink']),
            'calories' => $this->faker->numberBetween(50, 800),
            'meal_name' => $this->faker->word,
        ];
    }
}
