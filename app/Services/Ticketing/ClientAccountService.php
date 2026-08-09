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

    public function update($id, array $data): ClientAccount
    {
        $account = $this->repository->find($id);

        if ($account === null) {
            throw new \InvalidArgumentException('Client account not found.');
        }

        $account->fill($data);
        $this->repository->save($account);

        return $account;
    }

    public function toggleActive($id): ClientAccount
    {
        $account = $this->repository->find($id);

        if ($account === null) {
            throw new \InvalidArgumentException('Client account not found.');
        }

        $account->is_active = ! $account->is_active;
        $this->repository->save($account);

        return $account;
    }
}
