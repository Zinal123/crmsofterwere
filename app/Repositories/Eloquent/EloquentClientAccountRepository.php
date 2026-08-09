<?php

namespace App\Repositories\Eloquent;

use App\Models\ClientAccount;
use App\Repositories\Contracts\ClientAccountRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentClientAccountRepository implements ClientAccountRepositoryInterface
{
    public function allOrderedByLatest(): Collection
    {
        return ClientAccount::orderBy('id', 'desc')->get();
    }

    public function find($id): ?ClientAccount
    {
        return ClientAccount::find($id);
    }

    public function create(array $data): ClientAccount
    {
        return ClientAccount::create($data);
    }

    public function save(ClientAccount $account): void
    {
        $account->save();
    }
}
