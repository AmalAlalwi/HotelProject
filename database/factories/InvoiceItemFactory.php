<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\InvoiceItem;
use App\Models\Invoice;
use App\Models\Room;
use App\Models\Service;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InvoiceItem>
 */
class InvoiceItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $itemType = $this->faker->randomElement(['room', 'service']);
        $itemId = null;
        if ($itemType === 'room') {
            $itemId = Room::inRandomOrder()->first()->id;
        } else {
            $itemId = Service::inRandomOrder()->first()->id;
        }
        $quantity = $this->faker->numberBetween(1, 5);
        $unitPrice = $this->faker->randomFloat(2, 10, 500);
        $totalPrice = $quantity * $unitPrice;

        return [
            'invoice_id' => Invoice::inRandomOrder()->first()->id,
            'item_type' => $itemType,
            'item_id' => $itemId,
            'description' => $this->faker->sentence,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_price' => $totalPrice,
        ];
    }
}
