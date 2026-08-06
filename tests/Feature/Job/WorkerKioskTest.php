<?php

namespace Tests\Feature\Job;

use App\Models\Job;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkerKioskTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_worker_jobs_index_renders_the_kiosk_layout_not_the_admin_theme(): void
    {
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);

        $response = $this->actingAs($worker)->get(route('jobs.index'));

        $response->assertOk();
        $response->assertSee('worker-kiosk', false);
        $response->assertSee('My Jobs');
    }

    public function test_owner_jobs_index_still_renders_the_admin_theme(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('Owner');

        $response = $this->actingAs($owner)->get(route('jobs.index'));

        $response->assertOk();
        $response->assertDontSee('worker-kiosk', false);
        $response->assertSee('All Jobs');
    }

    public function test_worker_job_detail_renders_the_kiosk_layout(): void
    {
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);
        $job = Job::factory()->create(['status' => 'assigned', 'created_by' => $worker->id, 'assigned_to' => $worker->id]);

        $response = $this->actingAs($worker)->get(route('jobs.show', $job->id));

        $response->assertOk();
        $response->assertSee('worker-kiosk', false);
        $response->assertSee($job->title);
    }

    public function test_owner_job_detail_still_renders_the_admin_theme(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $job = Job::factory()->create(['status' => 'assigned', 'created_by' => $owner->id, 'assigned_to' => $owner->id]);

        $response = $this->actingAs($owner)->get(route('jobs.show', $job->id));

        $response->assertOk();
        $response->assertDontSee('worker-kiosk', false);
    }

    public function test_worker_kiosk_action_buttons_use_the_shopfloor_touch_target_class(): void
    {
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);
        $job = Job::factory()->create(['status' => 'assigned', 'created_by' => $worker->id, 'assigned_to' => $worker->id]);

        $response = $this->actingAs($worker)->get(route('jobs.show', $job->id));

        $response->assertOk();
        $response->assertSee('btn-shopfloor', false);
    }

    public function test_worker_kiosk_has_a_high_contrast_toggle(): void
    {
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);

        $response = $this->actingAs($worker)->get(route('jobs.index'));

        $response->assertOk();
        $response->assertSee('id="high-contrast-toggle"', false);
        $response->assertSee('worker-high-contrast', false);
        $response->assertSee('data-contrast', false);
    }
}
