<?php

namespace Tests\Feature\Job;

use App\Models\Job;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class JobPhotoUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_worker_uploads_photo_with_gps_and_gets_map_link(): void
    {
        Storage::fake('public');
        $this->seed(RolesAndPermissionsSeeder::class);
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);
        $job = Job::factory()->create(['status' => 'in_progress', 'assigned_to' => $worker->id]);
        $file = UploadedFile::fake()->image('proof.jpg', 2000, 1500);

        $response = $this->actingAs($worker)->post(route('jobs.photos.store', $job->id), [
            'photo' => $file,
            'latitude' => '23.0225000',
            'longitude' => '72.5714000',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('job_photos', [
            'job_id' => $job->id,
            'uploaded_by' => $worker->id,
            'location_captured' => true,
        ]);
        $photo = $job->photos()->first();
        $this->assertStringContainsString('23.0225', $photo->map_link);
        $this->assertDatabaseHas('job_audit_logs', ['job_id' => $job->id, 'action' => 'photo_uploaded']);
    }

    public function test_upload_fails_when_job_is_not_in_progress(): void
    {
        Storage::fake('public');
        $this->seed(RolesAndPermissionsSeeder::class);
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);
        $job = Job::factory()->create(['status' => 'assigned', 'assigned_to' => $worker->id]);
        $file = UploadedFile::fake()->image('proof.jpg', 800, 600);

        $response = $this->actingAs($worker)->postJson(route('jobs.photos.store', $job->id), ['photo' => $file]);

        $response->assertStatus(422);
        $this->assertDatabaseMissing('job_photos', ['job_id' => $job->id]);
    }

    public function test_upload_without_gps_is_flagged_not_silently_dropped(): void
    {
        Storage::fake('public');
        $this->seed(RolesAndPermissionsSeeder::class);
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);
        $job = Job::factory()->create(['status' => 'in_progress', 'assigned_to' => $worker->id]);
        $file = UploadedFile::fake()->image('proof.jpg', 800, 600);

        $response = $this->actingAs($worker)->post(route('jobs.photos.store', $job->id), ['photo' => $file]);

        $response->assertRedirect();
        $this->assertDatabaseHas('job_photos', ['job_id' => $job->id, 'location_captured' => false]);
    }
}
