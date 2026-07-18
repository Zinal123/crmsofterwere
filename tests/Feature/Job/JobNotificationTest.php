<?php

namespace Tests\Feature\Job;

use App\Models\Job;
use App\Models\User;
use App\Notifications\JobDecisionNotification;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class JobNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_approving_a_job_notifies_the_assigned_worker(): void
    {
        Notification::fake();
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $worker = User::factory()->create();
        $job = Job::factory()->create(['status' => 'pending_approval', 'created_by' => $worker->id, 'assigned_to' => $worker->id]);

        $this->actingAs($owner)->post(route('jobs.approve', $job->id));

        Notification::assertSentTo($worker, JobDecisionNotification::class, function ($notification) {
            return $notification->decision === 'approved';
        });
    }

    public function test_rejecting_a_job_notifies_the_creator_with_reason_in_database_payload(): void
    {
        Notification::fake();
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $worker = User::factory()->create();
        $job = Job::factory()->create(['status' => 'pending_approval', 'created_by' => $worker->id, 'assigned_to' => $worker->id]);

        $this->actingAs($owner)->post(route('jobs.reject', $job->id), ['rejection_reason' => 'Not needed']);

        Notification::assertSentTo($worker, JobDecisionNotification::class, function ($notification) {
            return $notification->decision === 'rejected';
        });
    }
}
