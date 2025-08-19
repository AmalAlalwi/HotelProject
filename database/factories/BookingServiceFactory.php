<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\BookingService;
use App\Models\User;
use App\Models\Service;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BookingService>
 */
class BookingServiceFactory extends Factory
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
        $service = Service::inRandomOrder()->first();
        $totalPrice = $service ? $service->price * \Illuminate\Support\Carbon::parse($checkInDate)->diffInDays($checkOutDate) : 0;

        return [
            'user_id' => User::whereNotIn('email', ['admin@gmail.com', 'employee@gmail.com'])->inRandomOrder()->first()->id,
            'service_id' => $service ? $service->id : Service::factory(),
            'check_in_date' => $checkInDate,
            'check_out_date' => $checkOutDate,
            'total_price' => $totalPrice,
        ];
    }
}
