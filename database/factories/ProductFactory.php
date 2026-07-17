<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition()
    {
        return [
            'name' => $this->faker->words(3, true),
            'rate' => $this->faker->numberBetween(1000, 900000),
            'unit' => 'Nos',
            'make' => strtoupper($this->faker->bothify('??-###')),
        ];
    }
}
