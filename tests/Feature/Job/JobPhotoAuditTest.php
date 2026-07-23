<?php

namespace Tests\Feature\Job;

use App\Models\Job;
use App\Models\JobPhoto;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobPhotoAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_uploading_a_job_photo_logs_a_created_entry(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $job = Job::factory()->create(['created_by' => $owner->id, 'assigned_to' => $owner->id]);

        $photo = JobPhoto::create([
            'job_id' => $job->id,
            'uploaded_by' => $owner->id,
            'path' => 'job-photos/1/example.jpg',
            'location_captured' => true,
            'captured_at' => now(),
        ]);

        $response = $this->actingAs($owner)->get(route('audit-logs.for-record', ['type' => 'job_photo', 'id' => $photo->id]));

        $response->assertOk();
        $response->assertSee('created');
    }
}
