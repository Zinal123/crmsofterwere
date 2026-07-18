<?php

namespace Tests\Feature\Job;

use App\Models\Job;
use App\Models\JobAuditLog;
use App\Models\JobPhoto;
use App\Models\Machine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobModelsMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_job_tables_migrate_and_relate_correctly(): void
    {
        $machine = Machine::factory()->create();
        $job = Job::factory()->create(['machine_id' => $machine->id]);
        $photo = JobPhoto::create([
            'job_id' => $job->id,
            'uploaded_by' => $job->created_by,
            'path' => 'job-photos/test.jpg',
            'captured_at' => now(),
        ]);
        $log = JobAuditLog::create([
            'job_id' => $job->id,
            'user_id' => $job->created_by,
            'action' => 'created',
            'description' => 'Job created',
        ]);

        $this->assertTrue($job->machine->is($machine));
        $this->assertTrue($job->photos->first()->is($photo));
        $this->assertTrue($job->auditLogs->first()->is($log));
    }

    public function test_job_audit_log_cannot_be_updated_or_deleted(): void
    {
        $job = Job::factory()->create();
        $log = JobAuditLog::create([
            'job_id' => $job->id,
            'user_id' => $job->created_by,
            'action' => 'created',
            'description' => 'Job created',
        ]);

        $this->expectException(\LogicException::class);
        $log->update(['description' => 'tampered']);
    }
}
