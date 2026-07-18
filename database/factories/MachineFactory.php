<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class MachineFactory extends Factory
{
    public function definition()
    {
        return [
            'name' => $this->faker->randomElement(['CNC Lathe #1', 'Fiber Laser Cutter', 'CNC Mill #2', 'Plasma Cutter']),
            'is_active' => true,
        ];
    }
}
