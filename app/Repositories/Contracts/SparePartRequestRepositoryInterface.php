<?php

namespace App\Repositories\Contracts;

use App\Models\SparePartRequest;
use Illuminate\Support\Collection;

interface SparePartRequestRepositoryInterface
{
    public function allWithDetails(): Collection;

    public function forClientAccount(int $clientAccountId): Collection;

    public function find($id): ?SparePartRequest;

    public function create(array $data): SparePartRequest;

    public function save(SparePartRequest $request): void;
}
