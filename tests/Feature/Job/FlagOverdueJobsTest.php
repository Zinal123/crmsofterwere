<?php

namespace Tests\Feature\Job;

use App\Models\Job;
use App\Models\User;
use App\Notifications\JobOverdueNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class FlagOverdueJobsTest extends TestCase
{
    use RefreshDatabase;

    public function test_flags_a_job_past_due_date_that_is_still_open(): void
    {
        $job = Job::factory()->create(['status' => 'in_progress', 'due_date' => now()->subDay()]);

        $this->artisan('jobs:flag-overdue')->assertSuccessful();

        $this->assertNotNull($job->fresh()->overdue_flagged_at);
    }

    public function test_does_not_flag_a_job_that_is_not_yet_due(): void
    {
        $job = Job::factory()->create(['status' => 'in_progress', 'due_date' => now()->addDay()]);

        $this->artisan('jobs:flag-overdue')->assertSuccessful();

        $this->assertNull($job->fresh()->overdue_flagged_at);
    }

    public function test_does_not_flag_a_completed_job_even_if_past_due(): void
    {
        $job = Job::factory()->create(['status' => 'completed', 'due_date' => now()->subWeek()]);

        $this->artisan('jobs:flag-overdue')->assertSuccessful();

        $this->assertNull($job->fresh()->overdue_flagged_at);
    }

    public function test_does_not_flag_a_rejected_job_even_if_past_due(): void
    {
        $job = Job::factory()->create(['status' => 'rejected', 'due_date' => now()->subWeek()]);

        $this->artisan('jobs:flag-overdue')->assertSuccessful();

        $this->assertNull($job->fresh()->overdue_flagged_at);
    }

    public function test_does_not_flag_a_job_with_no_due_date(): void
    {
        $job = Job::factory()->create(['status' => 'in_progress', 'due_date' => null]);

        $this->artisan('jobs:flag-overdue')->assertSuccessful();

        $this->assertNull($job->fresh()->overdue_flagged_at);
    }

    public function test_unflags_a_job_that_is_no_longer_overdue(): void
    {
        $job = Job::factory()->create([
            'status' => 'in_progress',
            'due_date' => now()->subDay(),
            'overdue_flagged_at' => now()->subDay(),
        ]);

        $job->update(['status' => 'completed']);

        $this->artisan('jobs:flag-overdue')->assertSuccessful();

        $this->assertNull($job->fresh()->overdue_flagged_at);
    }

    public function test_running_twice_does_not_reset_an_already_flagged_timestamp(): void
    {
        $job = Job::factory()->create(['status' => 'in_progress', 'due_date' => now()->subDay()]);

        $this->artisan('jobs:flag-overdue')->assertSuccessful();
        $firstFlaggedAt = $job->fresh()->overdue_flagged_at;

        $this->travel(1)->hour();
        $this->artisan('jobs:flag-overdue')->assertSuccessful();

        $this->assertTrue($firstFlaggedAt->equalTo($job->fresh()->overdue_flagged_at));
    }

    public function test_newly_flagging_a_job_overdue_notifies_the_assigned_worker(): void
    {
        Notification::fake();
        $worker = User::factory()->create();
        $job = Job::factory()->create(['status' => 'in_progress', 'assigned_to' => $worker->id, 'due_date' => now()->subDay()]);

        $this->artisan('jobs:flag-overdue')->assertSuccessful();

        Notification::assertSentTo($worker, JobOverdueNotification::class, fn ($n) => $n->job->id === $job->id);
    }

    public function test_running_twice_does_not_notify_again_for_an_already_flagged_job(): void
    {
        Notification::fake();
        $worker = User::factory()->create();
        Job::factory()->create(['status' => 'in_progress', 'assigned_to' => $worker->id, 'due_date' => now()->subDay()]);

        $this->artisan('jobs:flag-overdue');
        $this->travel(1)->hour();
        $this->artisan('jobs:flag-overdue');

        Notification::assertSentToTimes($worker, JobOverdueNotification::class, 1);
    }
}
