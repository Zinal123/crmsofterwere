<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ExpenseCategoryFactory extends Factory
{
    public function definition()
    {
        return [
            'name' => $this->faker->words(2, true),
            'type' => 'payment',
            'party_model' => null,
            'is_active' => true,
        ];
    }
}
