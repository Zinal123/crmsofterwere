<?php

namespace Tests\Feature\Job;

use App\Models\Job;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobReassignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_reassigns_an_in_progress_job(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $oldWorker = User::factory()->create();
        $newWorker = User::factory()->create();
        $job = Job::factory()->create(['status' => 'in_progress', 'assigned_to' => $oldWorker->id]);

        $response = $this->actingAs($owner)->post(route('jobs.reassign', $job->id), ['assigned_to' => $newWorker->id]);

        $response->assertRedirect();
        $this->assertEquals($newWorker->id, $job->fresh()->assigned_to);
        $this->assertEquals('in_progress', $job->fresh()->status);
        $this->assertDatabaseHas('job_audit_logs', ['job_id' => $job->id, 'action' => 'reassigned']);
    }

    public function test_cannot_reassign_a_completed_job(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $newWorker = User::factory()->create();
        $job = Job::factory()->create(['status' => 'completed']);

        $response = $this->actingAs($owner)->postJson(route('jobs.reassign', $job->id), ['assigned_to' => $newWorker->id]);

        $response->assertStatus(422);
    }

    public function test_worker_cannot_reassign(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);
        $job = Job::factory()->create(['status' => 'assigned', 'assigned_to' => $worker->id]);
        $newWorker = User::factory()->create();

        $response = $this->actingAs($worker)->post(route('jobs.reassign', $job->id), ['assigned_to' => $newWorker->id]);

        $response->assertForbidden();
    }
}
