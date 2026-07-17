<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvetryFactory extends Factory
{
    public function definition()
    {
        return [
            'product_id' => Product::factory(),
            'quantity' => $this->faker->numberBetween(1, 100),
            'vandername' => $this->faker->company(),
            'rate' => $this->faker->numberBetween(100, 50000),
        ];
    }
}
