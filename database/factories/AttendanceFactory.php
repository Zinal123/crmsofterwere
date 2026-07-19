<?php

namespace Database\Factories;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    public function definition()
    {
        return [
            'employee_id' => Employee::factory(),
            'date' => now()->toDateString(),
            'status' => 'present',
            'overtime_hours' => 0,
        ];
    }
}
