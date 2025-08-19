<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Room;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Room>
 */
class RoomFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'room_number' => $this->faker->unique()->randomNumber(3),
            'description' => $this->faker->sentence,
            'type' => $this->faker->randomElement(['single', 'double']),
            'price' => $this->faker->randomFloat(2, 50, 500),
            'is_available' => $this->faker->boolean,
            'img' => 'images/rooms/' . $this->faker->imageUrl(640, 480, 'room', true),
        ];
    }
}
