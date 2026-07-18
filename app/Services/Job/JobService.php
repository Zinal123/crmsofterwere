<?php

namespace App\Services\Job;

use App\Models\Job;
use App\Models\User;
use App\Repositories\Contracts\JobRepositoryInterface;

class JobService
{
    public function __construct(
        private JobRepositoryInterface $repository,
        private JobAuditLogger $auditLogger,
    ) {
    }

    private function assertHasLocation(array $data): void
    {
        $hasMachine = ! empty($data['machine_id']);
        $hasSite = ! empty($data['site_name']);

        if ($hasMachine === $hasSite) {
            throw new \InvalidArgumentException('Job must specify exactly one of machine_id or site_name.');
        }
    }

    public function createRequest(array $data, User $creator): Job
    {
        $this->assertHasLocation($data);

        $job = $this->repository->create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'machine_id' => $data['machine_id'] ?? null,
            'site_name' => $data['site_name'] ?? null,
            'priority' => $data['priority'] ?? 'medium',
            'due_date' => $data['due_date'] ?? null,
            'created_by' => $creator->id,
            'assigned_to' => $creator->id,
            'status' => 'pending_approval',
        ]);

        $this->auditLogger->log($job, $creator, 'created', "{$creator->name} requested a new job: {$job->title}");

        return $job;
    }

    public function createAssigned(array $data, User $creator): Job
    {
        $this->assertHasLocation($data);

        $job = $this->repository->create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'machine_id' => $data['machine_id'] ?? null,
            'site_name' => $data['site_name'] ?? null,
            'priority' => $data['priority'] ?? 'medium',
            'due_date' => $data['due_date'] ?? null,
            'created_by' => $creator->id,
            'assigned_to' => $data['assigned_to'],
            'status' => 'assigned',
        ]);

        $this->auditLogger->log($job, $creator, 'created', "{$creator->name} assigned a new job to worker #{$data['assigned_to']}: {$job->title}");

        return $job;
    }

    public function approve(Job $job, User $manager, ?int $reassignTo = null): Job
    {
        if ($job->status !== 'pending_approval') {
            throw new \InvalidArgumentException('Only a pending-approval job can be approved.');
        }

        $job->status = 'assigned';
        $job->decided_by = $manager->id;
        $job->decided_at = now();
        if ($reassignTo) {
            $job->assigned_to = $reassignTo;
        }
        $this->repository->save($job);

        $this->auditLogger->log($job, $manager, 'approved', "{$manager->name} approved the job request.");

        return $job;
    }

    public function reject(Job $job, User $manager, string $reason): Job
    {
        if ($job->status !== 'pending_approval') {
            throw new \InvalidArgumentException('Only a pending-approval job can be rejected.');
        }

        $job->status = 'rejected';
        $job->decided_by = $manager->id;
        $job->decided_at = now();
        $job->rejection_reason = $reason;
        $this->repository->save($job);

        $this->auditLogger->log($job, $manager, 'rejected', "{$manager->name} rejected the job request: {$reason}");

        return $job;
    }
}
