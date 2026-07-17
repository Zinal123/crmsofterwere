<?php

namespace Database\Factories;

use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    public function definition()
    {
        return [
            'invoice_id' => Invoice::factory(),
            'name' => $this->faker->company(),
            'phone' => $this->faker->numerify('##########'),
            'state' => 'Gujarat',
        ];
    }
}
