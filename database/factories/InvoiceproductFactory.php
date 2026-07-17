<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceproductFactory extends Factory
{
    public function definition()
    {
        return [
            'invoice_id' => Invoice::factory(),
            'product_name' => Product::factory(),
            'quantity' => $this->faker->numberBetween(1, 10),
            'totalamount' => $this->faker->numberBetween(1000, 100000),
        ];
    }
}
