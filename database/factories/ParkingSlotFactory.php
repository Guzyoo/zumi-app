<?php

namespace Database\Factories;

use App\Models\ParkingSlot;
use Illuminate\Database\Eloquent\Factories\Factory;

class ParkingSlotFactory extends Factory
{
    protected $model = ParkingSlot::class;

    public function definition(): array
    {
        return [
            'slot_code' => 'A' . $this->faker->unique()->numberBetween(1, 99),
            'is_occupied' => false,
            'last_updated_at' => now(),
        ];
    }
}