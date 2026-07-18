<?php

namespace App\Repositories\Contracts;

use App\Models\User;
use Illuminate\Support\Collection;

interface UserRepositoryInterface
{
    public function find($id): ?User;

    public function allWithRoles(): Collection;

    public function byRole(string $role): Collection;

    public function create(array $data): User;

    public function save(User $user): void;

    public function hasOtherActiveOwner($excludeUserId): bool;
}
