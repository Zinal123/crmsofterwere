<?php

namespace Tests\Feature\Workforce;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceRegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_shows_only_the_requested_employee_and_month(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $employee = Employee::factory()->create(['name' => 'Suresh Yadav']);
        $otherEmployee = Employee::factory()->create(['name' => 'Other Person']);
        Attendance::create(['employee_id' => $employee->id, 'date' => '2026-07-05', 'status' => 'present', 'marked_by' => $owner->id]);
        Attendance::create(['employee_id' => $employee->id, 'date' => '2026-06-05', 'status' => 'present', 'marked_by' => $owner->id]);
        Attendance::create(['employee_id' => $otherEmployee->id, 'date' => '2026-07-05', 'status' => 'present', 'marked_by' => $owner->id]);

        $response = $this->actingAs($owner)->get(route('attendance.register', ['employee' => $employee->id, 'year' => 2026, 'month' => 7]));

        $response->assertOk();
        $response->assertSee('Suresh Yadav');
        $response->assertViewHas('attendanceRows', function ($rows) {
            return $rows->count() === 1;
        });
    }
}
