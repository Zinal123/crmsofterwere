<?php

namespace Tests\Feature\Workforce;

use App\Models\Employee;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_bulk_marks_attendance_for_a_date(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $emp1 = Employee::factory()->create();
        $emp2 = Employee::factory()->create();

        $response = $this->actingAs($owner)->post(route('attendance.store'), [
            'date' => '2026-07-19',
            'rows' => [
                ['employee_id' => $emp1->id, 'status' => 'present', 'overtime_hours' => 1.5],
                ['employee_id' => $emp2->id, 'status' => 'half_day', 'overtime_hours' => 0],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('attendances', ['employee_id' => $emp1->id, 'date' => '2026-07-19', 'status' => 'present', 'overtime_hours' => 1.5]);
        $this->assertDatabaseHas('attendances', ['employee_id' => $emp2->id, 'date' => '2026-07-19', 'status' => 'half_day']);
    }

    public function test_remarking_the_same_date_updates_not_duplicates(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $employee = Employee::factory()->create();

        $this->actingAs($owner)->post(route('attendance.store'), [
            'date' => '2026-07-19',
            'rows' => [['employee_id' => $employee->id, 'status' => 'absent', 'overtime_hours' => 0]],
        ]);
        $this->actingAs($owner)->post(route('attendance.store'), [
            'date' => '2026-07-19',
            'rows' => [['employee_id' => $employee->id, 'status' => 'present', 'overtime_hours' => 2]],
        ]);

        $this->assertDatabaseCount('attendances', 1);
        $this->assertDatabaseHas('attendances', ['employee_id' => $employee->id, 'status' => 'present', 'overtime_hours' => 2]);
    }

    public function test_worker_cannot_mark_attendance(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);
        $employee = Employee::factory()->create();

        $response = $this->actingAs($worker)->post(route('attendance.store'), [
            'date' => '2026-07-19',
            'rows' => [['employee_id' => $employee->id, 'status' => 'present', 'overtime_hours' => 0]],
        ]);

        $response->assertForbidden();
    }
}
