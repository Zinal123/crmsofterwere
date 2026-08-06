<?php

namespace App\Services\Job;

use App\Models\Job;
use App\Models\Machine;
use App\Models\User;
use App\Notifications\JobAssignedNotification;
use App\Notifications\JobDecisionNotification;
use App\Notifications\MachineDownNotification;
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

        $job->assignee?->notify(new JobAssignedNotification($job, 'assigned'));

        return $job;
    }

    /**
     * Fast path from the Worker Kiosk: skip the normal job-request form
     * entirely, urgent priority, notify every Owner immediately. This is the
     * lightweight substitute for MachineMetrics-style downtime automation -
     * no edge hardware, just a faster entry point into the same job/approval
     * workflow that already exists.
     */
    public function flagMachineDown(Machine $machine, User $reporter, ?string $note): Job
    {
        $job = $this->repository->create([
            'title' => "Machine Down: {$machine->name}",
            'description' => $note,
            'machine_id' => $machine->id,
            'priority' => 'urgent',
            'created_by' => $reporter->id,
            'assigned_to' => $reporter->id,
            'status' => 'pending_approval',
        ]);

        $this->auditLogger->log($job, $reporter, 'created', "{$reporter->name} flagged \"{$machine->name}\" as down.");

        foreach (User::role('Owner')->get() as $owner) {
            $owner->notify(new MachineDownNotification($job));
        }

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

        $job->assignee?->notify(new JobDecisionNotification($job, 'approved'));

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

        $job->assignee?->notify(new JobDecisionNotification($job, 'rejected'));

        return $job;
    }

    private function assertActorCanAct(Job $job, User $actor): void
    {
        if ($actor->id !== $job->assigned_to && ! $actor->can('jobs.assign')) {
            throw new \Illuminate\Auth\Access\AuthorizationException('You are not assigned to this job.');
        }
    }

    public function start(Job $job, User $actor): Job
    {
        $this->assertActorCanAct($job, $actor);
        if ($job->status !== 'assigned') {
            throw new \InvalidArgumentException('Only an assigned job can be started.');
        }

        $job->status = 'in_progress';
        $this->repository->save($job);
        $this->auditLogger->log($job, $actor, 'status_changed', 'Status changed from Assigned to In Progress', ['from' => 'assigned', 'to' => 'in_progress']);

        return $job;
    }

    public function hold(Job $job, User $actor, string $reason): Job
    {
        $this->assertActorCanAct($job, $actor);
        if ($job->status !== 'in_progress') {
            throw new \InvalidArgumentException('Only an in-progress job can be put on hold.');
        }

        $job->status = 'on_hold';
        $job->on_hold_reason = $reason;
        $this->repository->save($job);
        $this->auditLogger->log($job, $actor, 'on_hold', "Job put on hold: {$reason}", ['from' => 'in_progress', 'to' => 'on_hold']);

        return $job;
    }

    public function resume(Job $job, User $actor): Job
    {
        $this->assertActorCanAct($job, $actor);
        if ($job->status !== 'on_hold') {
            throw new \InvalidArgumentException('Only an on-hold job can be resumed.');
        }

        $job->status = 'in_progress';
        $this->repository->save($job);
        $this->auditLogger->log($job, $actor, 'resumed', 'Job resumed from hold', ['from' => 'on_hold', 'to' => 'in_progress']);

        return $job;
    }

    public function complete(Job $job, User $actor, string $notes): Job
    {
        $this->assertActorCanAct($job, $actor);
        if ($job->status !== 'in_progress') {
            throw new \InvalidArgumentException('Only an in-progress job can be completed.');
        }
        if (! $job->photos()->exists()) {
            throw new \InvalidArgumentException('At least one proof photo is required to complete a job.');
        }

        $job->status = 'completed';
        $job->completion_notes = $notes;
        $job->completed_at = now();
        $this->repository->save($job);

        $this->auditLogger->log($job, $actor, 'completed', "{$actor->name} marked the job complete.", ['from' => 'in_progress', 'to' => 'completed']);

        return $job;
    }

    public function reassign(Job $job, User $manager, int $newAssigneeId): Job
    {
        if (! in_array($job->status, ['assigned', 'in_progress', 'on_hold'], true)) {
            throw new \InvalidArgumentException('Only an assigned, in-progress, or on-hold job can be reassigned.');
        }

        $previousAssignee = $job->assigned_to;
        $job->assigned_to = $newAssigneeId;
        $this->repository->save($job);

        $this->auditLogger->log($job, $manager, 'reassigned', "{$manager->name} reassigned the job.", [
            'from_user_id' => $previousAssignee,
            'to_user_id' => $newAssigneeId,
        ]);

        $job->assignee?->notify(new JobAssignedNotification($job, 'reassigned'));

        return $job;
    }
}
