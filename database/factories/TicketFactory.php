<?php

namespace Database\Factories;

use App\Models\ClientMachine;
use App\Models\TicketProblemType;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketFactory extends Factory
{
    public function definition()
    {
        return [
            'client_machine_id' => ClientMachine::factory(),
            'client_account_id' => function (array $attributes) {
                return ClientMachine::find($attributes['client_machine_id'])->client_account_id;
            },
            'problem_type_id' => TicketProblemType::factory(),
            'description' => $this->faker->sentence(),
            'status' => 'open',
        ];
    }
}
