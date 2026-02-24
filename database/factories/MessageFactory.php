<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Message>
 */
class MessageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'message' => $this->faker->paragraph,
            'time' => $this->faker->time(),
            'date' => $this->faker->date(),
            'read' => $this->faker->randomElement(['true', 'false']),
            'sender_id' => null, // Will be set in seeder
            'receiver_id' => null, // Will be set in seeder
            'sender_type' => $this->faker->randomElement(['user', 'doctor']),
        ];
    }
}
