<?php

namespace Tests\Feature\Job;

use App\Models\Job;
use App\Models\Machine;
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

    public function test_a_photo_can_be_tagged_as_before_or_after(): void
    {
        Storage::fake('public');
        $this->seed(RolesAndPermissionsSeeder::class);
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);
        $job = Job::factory()->create(['status' => 'in_progress', 'assigned_to' => $worker->id]);

        $this->actingAs($worker)->post(route('jobs.photos.store', $job->id), [
            'photo' => UploadedFile::fake()->image('before.jpg'),
            'latitude' => '23.0225000',
            'longitude' => '72.5714000',
            'stage' => 'before',
        ]);
        $this->actingAs($worker)->post(route('jobs.photos.store', $job->id), [
            'photo' => UploadedFile::fake()->image('after.jpg'),
            'latitude' => '23.0225000',
            'longitude' => '72.5714000',
            'stage' => 'after',
        ]);

        $this->assertDatabaseHas('job_photos', ['job_id' => $job->id, 'stage' => 'before']);
        $this->assertDatabaseHas('job_photos', ['job_id' => $job->id, 'stage' => 'after']);
    }

    public function test_a_photo_defaults_to_general_stage_when_not_specified(): void
    {
        Storage::fake('public');
        $this->seed(RolesAndPermissionsSeeder::class);
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);
        $job = Job::factory()->create(['status' => 'in_progress', 'assigned_to' => $worker->id]);

        $this->actingAs($worker)->post(route('jobs.photos.store', $job->id), [
            'photo' => UploadedFile::fake()->image('proof.jpg'),
            'latitude' => '23.0225000',
            'longitude' => '72.5714000',
        ]);

        $this->assertDatabaseHas('job_photos', ['job_id' => $job->id, 'stage' => 'general']);
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

    public function test_upload_without_gps_is_blocked_not_silently_accepted(): void
    {
        // GPS is mandatory as of the Photo/GPS integrity layer - a photo
        // with no location is no longer useful proof-of-work, so this now
        // blocks with a clear error instead of the old "flagged but
        // accepted" behavior.
        Storage::fake('public');
        $this->seed(RolesAndPermissionsSeeder::class);
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);
        $job = Job::factory()->create(['status' => 'in_progress', 'assigned_to' => $worker->id]);
        $file = UploadedFile::fake()->image('proof.jpg', 800, 600);

        $response = $this->actingAs($worker)->postJson(route('jobs.photos.store', $job->id), ['photo' => $file]);

        $response->assertStatus(422);
        $this->assertDatabaseMissing('job_photos', ['job_id' => $job->id]);
    }

    public function test_upload_stores_a_sha256_hash_of_the_stored_file(): void
    {
        Storage::fake('public');
        $this->seed(RolesAndPermissionsSeeder::class);
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);
        $job = Job::factory()->create(['status' => 'in_progress', 'assigned_to' => $worker->id]);
        $file = UploadedFile::fake()->image('proof.jpg', 800, 600);

        $this->actingAs($worker)->post(route('jobs.photos.store', $job->id), [
            'photo' => $file,
            'latitude' => '23.0225000',
            'longitude' => '72.5714000',
        ]);

        $photo = $job->photos()->first();
        $this->assertNotNull($photo->content_hash);
        $this->assertSame(64, strlen($photo->content_hash));
        $storedContents = Storage::disk('public')->get($photo->path);
        $this->assertSame(hash('sha256', $storedContents), $photo->content_hash);
    }

    public function test_photo_within_geofence_of_the_machine_is_not_flagged(): void
    {
        Storage::fake('public');
        $this->seed(RolesAndPermissionsSeeder::class);
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);
        $machine = Machine::factory()->create(['latitude' => 23.0225000, 'longitude' => 72.5714000]);
        $job = Job::factory()->create(['status' => 'in_progress', 'assigned_to' => $worker->id, 'machine_id' => $machine->id]);
        $file = UploadedFile::fake()->image('proof.jpg', 800, 600);

        $this->actingAs($worker)->post(route('jobs.photos.store', $job->id), [
            'photo' => $file,
            // A few meters from the machine's registered coordinates.
            'latitude' => '23.0225100',
            'longitude' => '72.5714100',
        ]);

        $photo = $job->photos()->first();
        $this->assertFalse($photo->location_flagged);
    }

    public function test_photo_outside_geofence_of_the_machine_is_flagged_but_still_accepted(): void
    {
        Storage::fake('public');
        $this->seed(RolesAndPermissionsSeeder::class);
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);
        $machine = Machine::factory()->create(['latitude' => 23.0225000, 'longitude' => 72.5714000]);
        $job = Job::factory()->create(['status' => 'in_progress', 'assigned_to' => $worker->id, 'machine_id' => $machine->id]);
        $file = UploadedFile::fake()->image('proof.jpg', 800, 600);

        $response = $this->actingAs($worker)->post(route('jobs.photos.store', $job->id), [
            'photo' => $file,
            // Roughly 5km north - well outside any reasonable geofence.
            'latitude' => '23.0675000',
            'longitude' => '72.5714000',
        ]);

        $response->assertRedirect();
        $photo = $job->photos()->first();
        $this->assertTrue($photo->location_flagged);
        $this->assertNotNull($photo->distance_from_machine_meters);
        $this->assertGreaterThan(1000, $photo->distance_from_machine_meters);
    }

    public function test_photo_is_not_flagged_when_the_machine_has_no_registered_coordinates(): void
    {
        Storage::fake('public');
        $this->seed(RolesAndPermissionsSeeder::class);
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);
        $machine = Machine::factory()->create(['latitude' => null, 'longitude' => null]);
        $job = Job::factory()->create(['status' => 'in_progress', 'assigned_to' => $worker->id, 'machine_id' => $machine->id]);
        $file = UploadedFile::fake()->image('proof.jpg', 800, 600);

        $this->actingAs($worker)->post(route('jobs.photos.store', $job->id), [
            'photo' => $file,
            'latitude' => '23.0225000',
            'longitude' => '72.5714000',
        ]);

        $photo = $job->photos()->first();
        $this->assertFalse($photo->location_flagged);
        $this->assertNull($photo->distance_from_machine_meters);
    }

    public function test_job_show_page_renders_camera_capture_ui_not_a_bare_file_picker(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);
        $job = Job::factory()->create(['status' => 'in_progress', 'assigned_to' => $worker->id]);

        $response = $this->actingAs($worker)->get(route('jobs.show', $job->id));

        $response->assertOk();
        $response->assertSee('id="camera-video"', false);
        $response->assertSee('id="capture-btn"', false);
        $response->assertSee('getUserMedia', false);
        // The plain file input exists only as a fallback, hidden until JS
        // determines getUserMedia is unavailable.
        $response->assertSee('id="camera-fallback-input"', false);
        $response->assertSee('camera-fallback-input', false);
    }

    public function test_job_show_page_renders_the_offline_sync_queue(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);
        $job = Job::factory()->create(['status' => 'in_progress', 'assigned_to' => $worker->id]);

        $response = $this->actingAs($worker)->get(route('jobs.show', $job->id));

        $response->assertOk();
        $response->assertSee('id="offline-queue-panel"', false);
        $response->assertSee('id="sync-now-btn"', false);
        $response->assertSee('indexedDB.open', false);
        $response->assertSee("window.addEventListener('online'", false);
    }
}
