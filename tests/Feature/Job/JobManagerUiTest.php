<?php

namespace Tests\Feature\Job;

use App\Models\Job;
use App\Models\Machine;
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

    public function test_jobs_index_links_the_machine_name_to_the_machines_page(): void
    {
        // No dedicated machine "show" page exists (index+modals is the
        // deliberate pattern) - the machine name was plain text with no way
        // to jump to that machine's own record at all.
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $machine = Machine::factory()->create();
        Job::factory()->create(['machine_id' => $machine->id]);

        $response = $this->actingAs($owner)->get(route('jobs.index'));

        $response->assertOk();
        $response->assertSee(route('machines.index') . '#machine-' . $machine->id, false);
    }

    public function test_jobs_index_machine_link_is_not_nested_inside_the_card_link(): void
    {
        // Real bug caught by browser-driven testing, not the HTTP test
        // client: the whole job card is wrapped in <a href="jobs.show">, and
        // the machine link is <a href="machines.index">. Nesting an <a>
        // inside another <a> is invalid HTML - browsers silently close the
        // outer anchor the moment the inner one opens, so everything after
        // the machine link (material-status badges, the rest of the card)
        // stopped being part of any link at all, breaking "click anywhere on
        // the card to open the job" for most of the card's surface. Assert
        // structurally, via a real DOM parser, that no <a> ever contains
        // another <a> - a plain string/substring assertion can't catch this.
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $machine = Machine::factory()->create();
        Job::factory()->create(['machine_id' => $machine->id]);

        $response = $this->actingAs($owner)->get(route('jobs.index'));
        $response->assertOk();

        $dom = new \DOMDocument();
        @$dom->loadHTML($response->getContent());
        $xpath = new \DOMXPath($dom);
        $nestedAnchors = $xpath->query('//a[.//a]');

        $this->assertSame(0, $nestedAnchors->length, 'Found an <a> containing another <a> - invalid HTML that browsers will silently mangle.');
    }

    public function test_jobs_index_hides_the_machine_link_from_a_role_without_machine_access(): void
    {
        // Manager has jobs.view-all (so sees this page) but not
        // jobs.manage-machines (which machines.index requires) - showing a
        // link that leads straight to a 403 would be worse than none.
        $this->seed(RolesAndPermissionsSeeder::class);
        $manager = User::factory()->create();
        $manager->syncRoles(['Manager']);
        $machine = Machine::factory()->create();
        Job::factory()->create(['machine_id' => $machine->id]);

        $response = $this->actingAs($manager)->get(route('jobs.index'));

        $response->assertOk();
        $response->assertDontSee(route('machines.index'), false);
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
