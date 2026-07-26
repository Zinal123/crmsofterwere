<?php

namespace App\Repositories\Eloquent;

use App\Models\ClientMachine;
use App\Repositories\Contracts\ClientMachineRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentClientMachineRepository implements ClientMachineRepositoryInterface
{
    public function allWithDetails(): Collection
    {
        return ClientMachine::with(['clientAccount', 'product'])->orderBy('id', 'desc')->get();
    }

    public function forClientAccount(int $clientAccountId): Collection
    {
        return ClientMachine::with('product')->where('client_account_id', $clientAccountId)->orderBy('id', 'desc')->get();
    }

    public function find($id): ?ClientMachine
    {
        return ClientMachine::with(['clientAccount', 'product'])->find($id);
    }

    public function create(array $data): ClientMachine
    {
        return ClientMachine::create($data);
    }
}
