<?php

namespace Tests\Feature\Job;

use App\Models\Job;
use App\Models\User;
use App\Services\Job\JobAuditLogger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobAuditLoggerTest extends TestCase
{
    use RefreshDatabase;

    public function test_log_creates_an_audit_row_with_actor_and_metadata(): void
    {
        $job = Job::factory()->create();
        $actor = User::factory()->create();
        $logger = app(JobAuditLogger::class);

        $entry = $logger->log($job, $actor, 'status_changed', 'Status changed from Assigned to In Progress', [
            'from' => 'assigned',
            'to' => 'in_progress',
        ]);

        $this->assertDatabaseHas('job_audit_logs', [
            'id' => $entry->id,
            'job_id' => $job->id,
            'user_id' => $actor->id,
            'action' => 'status_changed',
        ]);
        $this->assertEquals(['from' => 'assigned', 'to' => 'in_progress'], $entry->fresh()->metadata);
    }
}
