<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TicketProblemTypeFactory extends Factory
{
    public function definition()
    {
        return [
            'category' => $this->faker->randomElement(['electrical', 'mechanical']),
            'name' => $this->faker->words(3, true),
            'is_active' => true,
        ];
    }
}
