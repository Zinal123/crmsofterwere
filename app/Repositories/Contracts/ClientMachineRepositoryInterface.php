<?php

namespace App\Repositories\Contracts;

use App\Models\ClientMachine;
use Illuminate\Support\Collection;

interface ClientMachineRepositoryInterface
{
    public function allWithDetails(): Collection;

    public function forClientAccount(int $clientAccountId): Collection;

    public function find($id): ?ClientMachine;

    public function create(array $data): ClientMachine;

    public function save(ClientMachine $machine): void;
}
