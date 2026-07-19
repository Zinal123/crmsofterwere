<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'phone' => $this->faker->numerify('##########'),
            'joining_date' => now()->subMonths(3)->toDateString(),
            'pay_type' => 'daily',
            'pay_rate' => 600,
            'overtime_rate_per_hour' => 50,
            'is_active' => true,
        ];
    }
}
