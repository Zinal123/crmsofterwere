<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\RoleRepositoryInterface;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Role;

class EloquentRoleRepository implements RoleRepositoryInterface
{
    public function allWithPermissions(): Collection
    {
        return Role::with('permissions')->orderBy('name')->get();
    }

    public function find($id): ?Role
    {
        return Role::find($id);
    }

    public function create(string $name): Role
    {
        return Role::findOrCreate($name);
    }

    public function delete($id): void
    {
        Role::find($id)?->delete();
    }
}
