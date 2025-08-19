<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Invoice;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $totalPrice = $this->faker->randomFloat(2, 100, 1000);
        $paidAmount = $this->faker->randomFloat(2, 0, $totalPrice);

        $paymentStatus = 'unpaid';
        if ($paidAmount >= $totalPrice) {
            $paymentStatus = 'paid';
        } elseif ($paidAmount > 0) {
            $paymentStatus = 'partial';
        }

        return [
            'user_id' => User::inRandomOrder()->first()->id,
            'total_price' => $totalPrice,
            'paid_amount' => $paidAmount,
            'payment_status' => $paymentStatus,
            'issued_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
