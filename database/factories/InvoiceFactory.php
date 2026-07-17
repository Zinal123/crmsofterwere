<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    public function definition()
    {
        return [
            'invoice_id' => 'INV-' . $this->faker->unique()->numberBetween(1000, 9999),
            'date' => now()->toDateString(),
            'amount' => $this->faker->numberBetween(10000, 500000),
            'amountwithtax' => $this->faker->numberBetween(10000, 500000),
            'placesupply' => 'Gujarat',
            'remaining_amount' => 0,
        ];
    }
}
