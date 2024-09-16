<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Service>
 */
class ServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'category_id' => Category::inRandomOrder()->first()->id, // Wybiera losową istniejącą kategorię
            'address' => $this->faker->address(),
            'latitude' => $this->faker->latitude(),
            'longitude' => $this->faker->longitude(),
            'rating' => $this->faker->optional()->numberBetween(1, 5),
            'review_count' => $this->faker->optional()->numberBetween(0, 100),
            'opening_hours' => json_encode([
                'monday' => $this->faker->time('H:i') . '-' . $this->faker->time('H:i'),
                'tuesday' => $this->faker->time('H:i') . '-' . $this->faker->time('H:i'),
                'wednesday' => $this->faker->time('H:i') . '-' . $this->faker->time('H:i'),
                'thursday' => $this->faker->time('H:i') . '-' . $this->faker->time('H:i'),
                'friday' => $this->faker->time('H:i') . '-' . $this->faker->time('H:i'),
                'saturday' => $this->faker->boolean ? $this->faker->time('H:i') . '-' . $this->faker->time('H:i') : 'closed',
                'sunday' => 'closed',
            ]),
        ];
    }
}
