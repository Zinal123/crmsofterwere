<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Support\Tenancy\TenantScope;

class EloquentUserRepository implements UserRepositoryInterface
{
    public function __construct(private TenantScope $tenantScope)
    {
    }

    public function find($id): ?User
    {
        return $this->tenantScope->apply(User::query())->find($id);
    }
}
