<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Booking;
use App\Models\User;
use App\Models\Room;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Booking>
 */
class BookingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $checkInDate = $this->faker->dateTimeBetween('-1 month', '+3 months');
        $checkOutDate = $this->faker->dateTimeBetween($checkInDate->format('Y-m-d H:i:s') . ' +1 day', $checkInDate->format('Y-m-d H:i:s') . ' +7 days');
        $room = Room::inRandomOrder()->first();
        $totalPrice = $room ? $room->price * \Illuminate\Support\Carbon::parse($checkInDate)->diffInDays($checkOutDate) : 0;

        return [
            'user_id' => User::whereNotIn('email', ['admin@gmail.com', 'employee@gmail.com'])->inRandomOrder()->first()->id,
            'room_id' => $room ? $room->id : Room::factory(),
            'check_in_date' => $checkInDate,
            'check_out_date' => $checkOutDate,
            'total_price' => $totalPrice,
        ];
    }
}
