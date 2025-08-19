<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Service;

/**
 * @extends \Illuminate->Database\Eloquent\Factories\Factory<\App\Models\Service>
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
            'name' => $this->faker->unique()->word,
            'description' => $this->faker->sentence,
            'price' => $this->faker->randomFloat(2, 10, 200),
            'is_available' => $this->faker->boolean,
            'img' => 'images/services/' . $this->faker->imageUrl(640, 480, 'service', true),
        ];
    }
}
