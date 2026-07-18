<?php

namespace Tests\Feature\Job;

use App\Models\Job;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobWorkerUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_worker_sees_own_jobs_including_rejected_with_reason(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);
        Job::factory()->create(['created_by' => $worker->id, 'assigned_to' => $worker->id, 'status' => 'rejected', 'title' => 'Rejected Job Alpha', 'rejection_reason' => 'Not authorized']);

        $response = $this->actingAs($worker)->get(route('jobs.index'));

        $response->assertOk();
        $response->assertSee('Rejected Job Alpha');
        $response->assertSee('Not authorized');
    }

    public function test_worker_does_not_see_other_workers_jobs(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);
        $otherWorker = User::factory()->create();
        Job::factory()->create(['created_by' => $otherWorker->id, 'assigned_to' => $otherWorker->id, 'title' => 'Someone Elses Job']);

        $response = $this->actingAs($worker)->get(route('jobs.index'));

        $response->assertOk();
        $response->assertDontSee('Someone Elses Job');
    }

    public function test_job_detail_shows_start_button_for_assigned_status(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);
        $job = Job::factory()->create(['status' => 'assigned', 'assigned_to' => $worker->id]);

        $response = $this->actingAs($worker)->get(route('jobs.show', $job->id));

        $response->assertOk();
        $response->assertSee(route('jobs.start', $job->id), false);
    }
}
