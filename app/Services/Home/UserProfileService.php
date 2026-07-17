<?php

namespace App\Services\Home;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserProfileService
{
    public function __construct(private UserRepositoryInterface $repository)
    {
    }

    public function updateProfile($id, string $name, string $email, ?string $avatarPath): ?User
    {
        $user = $this->repository->find($id);
        $user->name = $name;
        $user->email = $email;

        if ($avatarPath) {
            $user->avatar = $avatarPath;
        }

        $user->update();

        return $user;
    }

    public function isCurrentPasswordCorrect(string $currentPassword): bool
    {
        // Preserves pre-existing behavior exactly: this checks the
        // currently-authenticated user's password, not the $id route
        // parameter's user - both are always the same user in practice
        // (the profile page only ever edits your own account) but this
        // matches what the original controller code did.
        return Hash::check($currentPassword, Auth::user()->password);
    }

    public function updatePassword($id, string $newPassword): ?User
    {
        $user = $this->repository->find($id);
        $user->password = Hash::make($newPassword);
        $user->update();

        return $user;
    }
}
