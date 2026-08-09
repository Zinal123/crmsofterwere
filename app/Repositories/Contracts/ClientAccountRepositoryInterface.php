<?php

namespace App\Repositories\Contracts;

use App\Models\ClientAccount;
use Illuminate\Support\Collection;

interface ClientAccountRepositoryInterface
{
    public function allOrderedByLatest(): Collection;

    public function find($id): ?ClientAccount;

    public function create(array $data): ClientAccount;

    public function save(ClientAccount $account): void;
}
