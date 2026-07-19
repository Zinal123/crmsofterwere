<?php

namespace Tests\Feature\Workforce;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkforceSidebarTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_sees_employees_and_attendance_links(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');

        $response = $this->actingAs($owner)->get(route('employees.index'));

        $response->assertOk();
        $response->assertSee(route('employees.index'), false);
        $response->assertSee(route('attendance.mark'), false);
    }

    public function test_worker_does_not_see_employees_link(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);

        $response = $this->actingAs($worker)->get(route('jobs.index'));

        $response->assertOk();
        $response->assertDontSee(route('employees.index'), false);
    }
}
