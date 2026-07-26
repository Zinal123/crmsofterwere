<?php

namespace App\Services\Ticketing;

use App\Models\ClientAccount;
use App\Repositories\Contracts\ClientAccountRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;

class ClientAccountService
{
    public function __construct(private ClientAccountRepositoryInterface $repository)
    {
    }

    public function listAll(): Collection
    {
        return $this->repository->allOrderedByLatest();
    }

    public function createAccount(array $data): ClientAccount
    {
        return $this->repository->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
            'is_active' => true,
        ]);
    }
}
