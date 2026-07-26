<?php

namespace Database\Factories;

use App\Models\ClientAccount;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClientMachineFactory extends Factory
{
    public function definition()
    {
        return [
            'client_account_id' => ClientAccount::factory(),
            'product_id' => Product::factory(),
            'serial_number' => strtoupper($this->faker->bothify('OMT-####-???')),
            'installed_at' => $this->faker->date(),
        ];
    }
}
