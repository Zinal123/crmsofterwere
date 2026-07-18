<?php

namespace App\Repositories\Eloquent;

use App\Models\Job;
use App\Models\User;
use App\Repositories\Contracts\JobRepositoryInterface;
use App\Support\Tenancy\TenantScope;
use Illuminate\Database\Eloquent\Collection;

class EloquentJobRepository implements JobRepositoryInterface
{
    public function __construct(private TenantScope $tenantScope)
    {
    }

    public function create(array $data): Job
    {
        return Job::create($data);
    }

    public function find($id): ?Job
    {
        return $this->tenantScope->apply(Job::query())->find($id);
    }

    public function save(Job $job): Job
    {
        $job->save();

        return $job;
    }

    public function allForUser(User $user): Collection
    {
        return $this->tenantScope->apply(
            Job::where('created_by', $user->id)->orWhere('assigned_to', $user->id)
        )->orderBy('id', 'desc')->get();
    }

    public function all(): Collection
    {
        return $this->tenantScope->apply(Job::orderBy('id', 'desc'))->get();
    }

    public function pendingApproval(): Collection
    {
        return $this->tenantScope->apply(Job::where('status', 'pending_approval'))->orderBy('id')->get();
    }

    public function ownerDashboardStats(): array
    {
        $byWorker = $this->tenantScope->apply(
            Job::where('status', 'completed')->whereDate('completed_at', today())
        )->with('assignee')->get()->groupBy('assignee.name')->map->count();

        return [
            'completed_today' => $this->tenantScope->apply(
                Job::where('status', 'completed')->whereDate('completed_at', today())
            )->count(),
            'pending_approval' => $this->tenantScope->apply(Job::where('status', 'pending_approval'))->count(),
            'pending_completion' => $this->tenantScope->apply(
                Job::whereIn('status', ['assigned', 'in_progress', 'on_hold'])
            )->count(),
            'by_worker' => $byWorker,
        ];
    }
}
