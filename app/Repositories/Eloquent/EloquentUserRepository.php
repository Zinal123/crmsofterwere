<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Support\Tenancy\TenantScope;
use Illuminate\Support\Collection;

class EloquentUserRepository implements UserRepositoryInterface
{
    public function __construct(private TenantScope $tenantScope)
    {
    }

    public function find($id): ?User
    {
        return $this->tenantScope->apply(User::query())->find($id);
    }

    public function allWithRoles(): Collection
    {
        return $this->tenantScope->apply(User::with('roles'))->orderBy('name')->get();
    }

    public function create(array $data): User
    {
        return User::create($data);
    }

    public function save(User $user): void
    {
        $user->save();
    }

    public function hasOtherActiveOwner($excludeUserId): bool
    {
        return $this->tenantScope->apply(User::role('Owner'))
            ->where('is_active', true)
            ->where('id', '!=', $excludeUserId)
            ->exists();
    }
}
