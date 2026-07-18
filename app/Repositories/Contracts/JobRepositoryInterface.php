<?php

namespace App\Repositories\Contracts;

use App\Models\Job;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface JobRepositoryInterface
{
    public function create(array $data): Job;

    public function find($id): ?Job;

    public function save(Job $job): Job;

    public function allForUser(User $user): Collection;

    public function all(): Collection;

    public function pendingApproval(): Collection;

    public function ownerDashboardStats(): array;
}
