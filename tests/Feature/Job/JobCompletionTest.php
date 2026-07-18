<?php

namespace Tests\Feature\Job;

use App\Models\Job;
use App\Models\JobPhoto;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobCompletionTest extends TestCase
{
    use RefreshDatabase;

    private function inProgressJobWithPhoto(): array
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);
        $job = Job::factory()->create(['status' => 'in_progress', 'assigned_to' => $worker->id]);
        JobPhoto::create([
            'job_id' => $job->id,
            'uploaded_by' => $worker->id,
            'path' => 'job-photos/test.jpg',
            'captured_at' => now(),
        ]);

        return [$worker, $job];
    }

    public function test_complete_requires_notes_and_at_least_one_photo(): void
    {
        [$worker, $job] = $this->inProgressJobWithPhoto();

        $response = $this->actingAs($worker)->post(route('jobs.complete', $job->id), [
            'completion_notes' => 'Replaced the coolant hose and tested at full RPM.',
        ]);

        $response->assertRedirect();
        $this->assertEquals('completed', $job->fresh()->status);
        $this->assertNotNull($job->fresh()->completed_at);
        $this->assertDatabaseHas('job_audit_logs', ['job_id' => $job->id, 'action' => 'completed']);
    }

    public function test_complete_fails_without_any_photo(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);
        $job = Job::factory()->create(['status' => 'in_progress', 'assigned_to' => $worker->id]);

        $response = $this->actingAs($worker)->postJson(route('jobs.complete', $job->id), [
            'completion_notes' => 'Done',
        ]);

        $response->assertStatus(422);
        $this->assertEquals('in_progress', $job->fresh()->status);
    }

    public function test_complete_fails_without_notes(): void
    {
        [$worker, $job] = $this->inProgressJobWithPhoto();

        $response = $this->actingAs($worker)->postJson(route('jobs.complete', $job->id), []);

        $response->assertStatus(422);
    }
}
