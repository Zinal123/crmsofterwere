<?php

namespace Tests\Feature\Job;

use App\Models\Machine;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobCreationTest extends TestCase
{
    use RefreshDatabase;

    private function actingWorker(): User
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $user = User::factory()->create();
        $user->syncRoles(['Worker']);

        return $user;
    }

    private function actingOwner(): User
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('Owner');

        return $user;
    }

    public function test_worker_creating_a_job_lands_in_pending_approval_assigned_to_self(): void
    {
        $worker = $this->actingWorker();
        $machine = Machine::factory()->create();

        $response = $this->actingAs($worker)->post(route('jobs.store'), [
            'title' => 'Fix coolant leak',
            'machine_id' => $machine->id,
            'priority' => 'high',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('jobs', [
            'title' => 'Fix coolant leak',
            'status' => 'pending_approval',
            'created_by' => $worker->id,
            'assigned_to' => $worker->id,
        ]);
        $this->assertDatabaseHas('job_audit_logs', ['action' => 'created']);
    }

    public function test_manager_direct_assign_skips_approval(): void
    {
        $owner = $this->actingOwner();
        $worker = User::factory()->create();
        $machine = Machine::factory()->create();

        $response = $this->actingAs($owner)->post(route('jobs.store'), [
            'title' => 'Recalibrate laser head',
            'machine_id' => $machine->id,
            'priority' => 'medium',
            'assigned_to' => $worker->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('jobs', [
            'title' => 'Recalibrate laser head',
            'status' => 'assigned',
            'assigned_to' => $worker->id,
        ]);
    }

    public function test_job_requires_exactly_one_of_machine_or_site(): void
    {
        $worker = $this->actingWorker();

        $response = $this->actingAs($worker)->postJson(route('jobs.store'), [
            'title' => 'No location given',
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseMissing('jobs', ['title' => 'No location given']);
    }
}
