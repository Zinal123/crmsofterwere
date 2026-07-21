<?php

namespace Tests\Feature\Workforce;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_marking_attendance_twice_logs_the_status_change(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $employee = Employee::factory()->create();
        $attendance = Attendance::create(['employee_id' => $employee->id, 'date' => '2026-07-01', 'status' => 'present', 'marked_by' => $owner->id]);

        $attendance->update(['status' => 'absent']);

        $response = $this->actingAs($owner)->get(route('audit-logs.for-record', ['type' => 'attendance', 'id' => $attendance->id]));

        $response->assertOk();
        $response->assertSee('present');
        $response->assertSee('absent');
    }

    public function test_register_page_has_a_history_trigger_per_row(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $employee = Employee::factory()->create();
        $attendance = Attendance::create(['employee_id' => $employee->id, 'date' => '2026-07-10', 'status' => 'present', 'marked_by' => $owner->id]);

        $response = $this->actingAs($owner)->get(route('attendance.register', ['employee' => $employee->id, 'year' => 2026, 'month' => 7]));

        $response->assertOk();
        $response->assertSee('data-audit-id="' . $attendance->id . '"', false);
    }
}
