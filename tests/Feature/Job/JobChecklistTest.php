<?php

namespace Tests\Feature\Job;

use App\Models\Job;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobChecklistTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_the_creator_can_add_a_checklist_item(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);
        $job = Job::factory()->create(['created_by' => $owner->id, 'assigned_to' => $worker->id, 'status' => 'assigned']);

        $response = $this->actingAs($owner)->post(route('jobs.checklist.store', $job->id), [
            'description' => 'Isolate power before opening the enclosure',
        ]);

        $response->assertRedirect(route('jobs.show', $job->id));
        $this->assertDatabaseHas('job_checklist_items', [
            'job_id' => $job->id,
            'description' => 'Isolate power before opening the enclosure',
            'is_completed' => false,
        ]);
    }

    public function test_a_worker_with_no_stake_in_the_job_cannot_add_a_checklist_item(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $assignedWorker = User::factory()->create();
        $assignedWorker->syncRoles(['Worker']);
        $otherWorker = User::factory()->create();
        $otherWorker->syncRoles(['Worker']);
        $job = Job::factory()->create(['created_by' => $owner->id, 'assigned_to' => $assignedWorker->id, 'status' => 'assigned']);

        $response = $this->actingAs($otherWorker)->post(route('jobs.checklist.store', $job->id), [
            'description' => 'Should not be allowed',
        ]);

        $response->assertForbidden();
    }

    public function test_the_assigned_worker_can_check_off_an_item(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);
        $job = Job::factory()->create(['created_by' => $owner->id, 'assigned_to' => $worker->id, 'status' => 'in_progress']);
        $item = $job->checklistItems()->create(['description' => 'Check coolant level']);

        $response = $this->actingAs($worker)->post(route('jobs.checklist.toggle', [$job->id, $item->id]));

        $response->assertRedirect(route('jobs.show', $job->id));
        $this->assertTrue($item->fresh()->is_completed);
        $this->assertSame($worker->id, $item->fresh()->completed_by);
        $this->assertNotNull($item->fresh()->completed_at);
    }

    public function test_toggling_an_already_completed_item_marks_it_incomplete_again(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);
        $job = Job::factory()->create(['created_by' => $owner->id, 'assigned_to' => $worker->id, 'status' => 'in_progress']);
        $item = $job->checklistItems()->create([
            'description' => 'Check coolant level',
            'is_completed' => true,
            'completed_by' => $worker->id,
            'completed_at' => now(),
        ]);

        $this->actingAs($worker)->post(route('jobs.checklist.toggle', [$job->id, $item->id]));

        $this->assertFalse($item->fresh()->is_completed);
        $this->assertNull($item->fresh()->completed_by);
        $this->assertNull($item->fresh()->completed_at);
    }

    public function test_a_worker_with_no_stake_in_the_job_cannot_toggle_an_item(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $assignedWorker = User::factory()->create();
        $assignedWorker->syncRoles(['Worker']);
        $otherWorker = User::factory()->create();
        $otherWorker->syncRoles(['Worker']);
        $job = Job::factory()->create(['created_by' => $owner->id, 'assigned_to' => $assignedWorker->id, 'status' => 'in_progress']);
        $item = $job->checklistItems()->create(['description' => 'Check coolant level']);

        $response = $this->actingAs($otherWorker)->post(route('jobs.checklist.toggle', [$job->id, $item->id]));

        $response->assertForbidden();
    }

    public function test_job_detail_page_shows_checklist_items(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $job = Job::factory()->create(['created_by' => $owner->id, 'assigned_to' => $owner->id, 'status' => 'assigned']);
        $job->checklistItems()->create(['description' => 'Lockout/tagout the main breaker']);

        $response = $this->actingAs($owner)->get(route('jobs.show', $job->id));

        $response->assertOk();
        $response->assertSee('Lockout/tagout the main breaker');
    }
}
