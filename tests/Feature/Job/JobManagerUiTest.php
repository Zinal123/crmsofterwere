<?php

namespace Tests\Feature\Job;

use App\Models\Job;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobManagerUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_pending_approval_queue_lists_requests(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        Job::factory()->create(['status' => 'pending_approval', 'title' => 'Queue Item One']);

        $response = $this->actingAs($owner)->get(route('jobs.pending-approval'));

        $response->assertOk();
        $response->assertSee('Queue Item One');
    }

    public function test_manager_sees_approve_reject_actions_on_pending_job(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $job = Job::factory()->create(['status' => 'pending_approval']);

        $response = $this->actingAs($owner)->get(route('jobs.show', $job->id));

        $response->assertOk();
        $response->assertSee(route('jobs.approve', $job->id), false);
        $response->assertSee(route('jobs.reject', $job->id), false);
    }

    public function test_manager_sees_audit_trail_on_job_detail(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $job = Job::factory()->create(['status' => 'assigned']);
        app(\App\Services\Job\JobAuditLogger::class)->log($job, $owner, 'assigned', 'Manager assigned this job directly.');

        $response = $this->actingAs($owner)->get(route('jobs.show', $job->id));

        $response->assertOk();
        $response->assertSee('Manager assigned this job directly.');
    }
}
