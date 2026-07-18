<?php

namespace Tests\Feature\Job;

use App\Models\Job;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobStatusTransitionTest extends TestCase
{
    use RefreshDatabase;

    private function assignedWorker(): array
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);
        $job = Job::factory()->create(['status' => 'assigned', 'assigned_to' => $worker->id]);

        return [$worker, $job];
    }

    public function test_assigned_worker_can_start_job(): void
    {
        [$worker, $job] = $this->assignedWorker();

        $response = $this->actingAs($worker)->post(route('jobs.start', $job->id));

        $response->assertRedirect();
        $this->assertEquals('in_progress', $job->fresh()->status);
        $this->assertDatabaseHas('job_audit_logs', ['job_id' => $job->id, 'action' => 'status_changed']);
    }

    public function test_other_worker_cannot_start_someone_elses_job(): void
    {
        [, $job] = $this->assignedWorker();
        $otherWorker = User::factory()->create();
        $otherWorker->syncRoles(['Worker']);

        $response = $this->actingAs($otherWorker)->post(route('jobs.start', $job->id));

        $response->assertForbidden();
    }

    public function test_hold_requires_reason_and_moves_from_in_progress(): void
    {
        [$worker, $job] = $this->assignedWorker();
        $job->update(['status' => 'in_progress']);

        $response = $this->actingAs($worker)->post(route('jobs.hold', $job->id), ['on_hold_reason' => 'Waiting for replacement part']);

        $response->assertRedirect();
        $this->assertEquals('on_hold', $job->fresh()->status);
        $this->assertEquals('Waiting for replacement part', $job->fresh()->on_hold_reason);
    }

    public function test_hold_without_reason_fails(): void
    {
        [$worker, $job] = $this->assignedWorker();
        $job->update(['status' => 'in_progress']);

        $response = $this->actingAs($worker)->postJson(route('jobs.hold', $job->id), []);

        $response->assertStatus(422);
    }

    public function test_resume_moves_on_hold_back_to_in_progress(): void
    {
        [$worker, $job] = $this->assignedWorker();
        $job->update(['status' => 'on_hold', 'on_hold_reason' => 'parts']);

        $response = $this->actingAs($worker)->post(route('jobs.resume', $job->id));

        $response->assertRedirect();
        $this->assertEquals('in_progress', $job->fresh()->status);
    }
}
