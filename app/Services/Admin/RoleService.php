<?php

namespace App\Services\Admin;

use App\Repositories\Contracts\RoleRepositoryInterface;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleService
{
    public function __construct(private RoleRepositoryInterface $repository)
    {
    }

    public function listRolesWithPermissions(): Collection
    {
        return $this->repository->allWithPermissions();
    }

    public function allPermissionNames(): array
    {
        return RolesAndPermissionsSeeder::PERMISSIONS;
    }

    public function createRole(string $name): Role
    {
        return $this->repository->create($name);
    }

    public function deleteRole($id): void
    {
        $role = $this->repository->find($id);

        if ($role === null) {
            throw new \InvalidArgumentException('Role not found.');
        }

        if ($role->name === 'Owner') {
            throw new \InvalidArgumentException('The Owner role cannot be deleted.');
        }

        if ($this->repository->hasAssignedUsers($id)) {
            throw new \InvalidArgumentException('This role has users assigned to it. Reassign those users before deleting the role.');
        }

        $this->repository->delete($id);
    }

    public function togglePermission($roleId, string $permissionName): bool
    {
        $role = $this->repository->find($roleId);

        if ($role === null) {
            throw new \InvalidArgumentException('Role not found.');
        }

        if (!in_array($permissionName, RolesAndPermissionsSeeder::PERMISSIONS, true)) {
            throw new \InvalidArgumentException('Invalid permission name.');
        }

        Permission::findOrCreate($permissionName);

        if ($role->hasPermissionTo($permissionName)) {
            $role->revokePermissionTo($permissionName);
            return false;
        }

        $role->givePermissionTo($permissionName);
        return true;
    }
}
