<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Profile>
 */
class ProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'birth_date' => fake()->date(),
            'gender' => fake()->randomElement(['male', 'female']),
            'avatar' => fake()->imageUrl(200, 200, 'people'),
            'bio' => fake()->text(200),
            'country' => fake()->country(),
            'city' => fake()->city(),
            'postal_code' => fake()->postcode(),
        ];
    }
}
