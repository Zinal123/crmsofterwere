<?php

namespace Tests\Feature\Job;

use App\Models\Job;
use App\Models\JobPhoto;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobPdfReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_assigned_worker_can_download_the_completion_report_for_a_completed_job(): void
    {
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);
        $job = Job::factory()->create([
            'status' => 'completed',
            'created_by' => $worker->id,
            'assigned_to' => $worker->id,
            'completion_notes' => 'Replaced the fuse and tested run cycle.',
        ]);
        JobPhoto::create([
            'job_id' => $job->id,
            'uploaded_by' => $worker->id,
            'path' => 'job-photos/example.jpg',
            'captured_at' => now(),
        ]);

        $response = $this->actingAs($worker)->get(route('jobs.pdf', $job->id));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_report_is_not_available_until_the_job_is_completed(): void
    {
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);
        $job = Job::factory()->create([
            'status' => 'in_progress',
            'created_by' => $worker->id,
            'assigned_to' => $worker->id,
        ]);

        $response = $this->actingAs($worker)->get(route('jobs.pdf', $job->id));

        $response->assertRedirect(route('jobs.show', $job->id));
        $response->assertSessionHas('error');
    }

    public function test_worker_cannot_download_the_report_for_someone_elses_job(): void
    {
        $owner = User::factory()->create();
        $otherWorker = User::factory()->create();
        $otherWorker->syncRoles(['Worker']);
        $job = Job::factory()->create([
            'status' => 'completed',
            'created_by' => $owner->id,
            'assigned_to' => $owner->id,
        ]);

        $response = $this->actingAs($otherWorker)->get(route('jobs.pdf', $job->id));

        $response->assertForbidden();
    }
}
