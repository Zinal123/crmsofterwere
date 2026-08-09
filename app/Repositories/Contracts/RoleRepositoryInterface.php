<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;
use Spatie\Permission\Models\Role;

interface RoleRepositoryInterface
{
    public function allWithPermissions(): Collection;

    public function find($id): ?Role;

    public function create(string $name): Role;

    public function delete($id): void;

    public function hasAssignedUsers($roleId): bool;

    public function save(Role $role): void;
}
