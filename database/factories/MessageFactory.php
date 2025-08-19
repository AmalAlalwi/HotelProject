<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Message;
use App\Models\User;

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
        $sender = User::whereNotIn('email', ['admin@gmail.com', 'employee@gmail.com'])->inRandomOrder()->first();
        $employeeUser = User::where('email', 'employee@gmail.com')->first();
        $receiver = $employeeUser ? $employeeUser : User::factory()->state(['email' => 'employee@example.com', 'password' => bcrypt('password')]); // Fallback if employee doesn't exist

        return [
            'sender_id' => $sender ? $sender->id : User::factory(),
            'receiver_id' => $receiver->id,
            'message' => $this->faker->sentence,
            'read_at' => $this->faker->boolean(50) ? $this->faker->dateTimeBetween('-1 month', 'now') : null,
        ];
    }
}
