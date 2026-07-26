<?php

namespace App\Repositories\Eloquent;

use App\Models\SparePartRequest;
use App\Repositories\Contracts\SparePartRequestRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentSparePartRequestRepository implements SparePartRequestRepositoryInterface
{
    private const WITH = ['clientMachine.product', 'clientAccount', 'product'];

    public function allWithDetails(): Collection
    {
        return SparePartRequest::with(self::WITH)->orderBy('id', 'desc')->get();
    }

    public function forClientAccount(int $clientAccountId): Collection
    {
        return SparePartRequest::with(self::WITH)->where('client_account_id', $clientAccountId)->orderBy('id', 'desc')->get();
    }

    public function find($id): ?SparePartRequest
    {
        return SparePartRequest::with(self::WITH)->find($id);
    }

    public function create(array $data): SparePartRequest
    {
        return SparePartRequest::create($data);
    }

    public function save(SparePartRequest $request): void
    {
        $request->save();
    }
}
