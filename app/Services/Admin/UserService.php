<?php

namespace App\Services\Admin;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\Auditing\AuditLogService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function __construct(
        private UserRepositoryInterface $repository,
        private AuditLogService $auditLog,
    ) {
    }

    public function listAllWithRoles(): Collection
    {
        return $this->repository->allWithRoles();
    }

    public function createUser(array $data): User
    {
        $user = $this->repository->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'is_active' => true,
        ]);

        $user->syncRoles([$data['role']]);

        return $user;
    }

    public function updateRoleAndStatus($id, string $roleName, bool $isActive): User
    {
        $user = $this->repository->find($id);

        if ($user === null) {
            throw new \InvalidArgumentException('User not found.');
        }

        $losingOwnerRole = $user->hasRole('Owner') && $roleName !== 'Owner';
        $beingDeactivated = $user->is_active && !$isActive;

        if (($losingOwnerRole || $beingDeactivated) && $this->isLastActiveOwner($user)) {
            throw new \InvalidArgumentException('At least one active Owner must remain. Assign another user the Owner role before changing or deactivating this one.');
        }

        $oldRole = $user->roles->pluck('name')->first();

        if ($oldRole !== $roleName) {
            $this->auditLog->log($user, 'updated', 'role', $oldRole, $roleName);
        }

        $user->syncRoles([$roleName]);
        $user->is_active = $isActive;
        $this->repository->save($user);

        return $user;
    }

    private function isLastActiveOwner(User $user): bool
    {
        if (!$user->hasRole('Owner')) {
            return false;
        }

        return !$this->repository->hasOtherActiveOwner($user->id);
    }
}
