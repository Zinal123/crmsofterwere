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
        $this->repository->delete($id);
    }

    public function togglePermission($roleId, string $permissionName): bool
    {
        $role = $this->repository->find($roleId);
        Permission::findOrCreate($permissionName);

        if ($role->hasPermissionTo($permissionName)) {
            $role->revokePermissionTo($permissionName);
            return false;
        }

        $role->givePermissionTo($permissionName);
        return true;
    }
}
