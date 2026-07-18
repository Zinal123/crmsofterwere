<?php

namespace App\Services\Job;

use App\Models\Job;
use App\Models\JobAuditLog;
use App\Models\User;

class JobAuditLogger
{
    public function log(Job $job, User $actor, string $action, string $description, array $metadata = []): JobAuditLog
    {
        return JobAuditLog::create([
            'job_id' => $job->id,
            'user_id' => $actor->id,
            'action' => $action,
            'description' => $description,
            'metadata' => $metadata,
        ]);
    }
}
