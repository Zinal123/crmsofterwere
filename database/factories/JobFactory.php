<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class JobFactory extends Factory
{
    public function definition()
    {
        $creator = User::factory()->create();

        return [
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(),
            'site_name' => $this->faker->company(),
            'created_by' => $creator->id,
            'assigned_to' => $creator->id,
            'priority' => 'medium',
            'status' => 'pending_approval',
        ];
    }
}
