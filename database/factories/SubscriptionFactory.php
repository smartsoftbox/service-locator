<?php

namespace Database\Factories;

use App\Models\Service;
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
            'service_id' => Service::inRandomOrder()->first()->id, // Wybiera losową istniejącą usługę
            'plan' => $this->faker->randomElement(['free', 'premium']),  // Ensure 'free' or 'premium' only
            'started_at' => $this->faker->dateTimeThisYear(),
            'expires_at' => $this->faker->dateTimeBetween('+1 month', '+1 year'),
        ];
    }
}
