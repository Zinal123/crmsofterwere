<?php

namespace Tests\Feature\Job;

use App\Models\Job;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_approves_pending_job(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $job = Job::factory()->create(['status' => 'pending_approval']);

        $response = $this->actingAs($owner)->post(route('jobs.approve', $job->id));

        $response->assertRedirect();
        $this->assertEquals('assigned', $job->fresh()->status);
        $this->assertEquals($owner->id, $job->fresh()->decided_by);
        $this->assertNotNull($job->fresh()->decided_at);
        $this->assertDatabaseHas('job_audit_logs', ['job_id' => $job->id, 'action' => 'approved']);
    }

    public function test_manager_rejects_pending_job_with_reason(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $job = Job::factory()->create(['status' => 'pending_approval']);

        $response = $this->actingAs($owner)->post(route('jobs.reject', $job->id), [
            'rejection_reason' => 'Duplicate of job #12',
        ]);

        $response->assertRedirect();
        $this->assertEquals('rejected', $job->fresh()->status);
        $this->assertEquals('Duplicate of job #12', $job->fresh()->rejection_reason);
        $this->assertDatabaseHas('job_audit_logs', ['job_id' => $job->id, 'action' => 'rejected']);
    }

    public function test_rejection_requires_a_reason(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $job = Job::factory()->create(['status' => 'pending_approval']);

        $response = $this->actingAs($owner)->postJson(route('jobs.reject', $job->id), []);

        $response->assertStatus(422);
        $this->assertEquals('pending_approval', $job->fresh()->status);
    }

    public function test_worker_cannot_approve(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);
        $job = Job::factory()->create(['status' => 'pending_approval']);

        $response = $this->actingAs($worker)->post(route('jobs.approve', $job->id));

        $response->assertForbidden();
    }
}
