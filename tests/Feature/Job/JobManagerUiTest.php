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

    public function test_a_viewer_without_reassign_permission_can_still_see_who_a_job_is_assigned_to(): void
    {
        // The assignee's name previously only ever appeared inside the
        // reassignment <select>, which is gated behind jobs.assign - so
        // anyone with view access but not assign access (no seeded role
        // today has exactly that combination, but it's a real permission
        // boundary, not a hypothetical one) had no way to see who was
        // working a job at all.
        $this->seed(RolesAndPermissionsSeeder::class);
        $viewer = User::factory()->create();
        // UserFactory auto-assigns Owner (which has every permission) - strip
        // it first so this user genuinely has only jobs.view-all.
        $viewer->syncRoles([]);
        $viewer->givePermissionTo(['jobs.view-all']);
        $worker = User::factory()->create(['name' => 'Ramesh Patel']);
        $job = Job::factory()->create(['status' => 'assigned', 'assigned_to' => $worker->id]);

        $response = $this->actingAs($viewer)->get(route('jobs.show', $job->id));

        $response->assertOk();
        $response->assertDontSee(route('jobs.reassign', $job->id), false);
        $response->assertSee('Ramesh Patel');
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
