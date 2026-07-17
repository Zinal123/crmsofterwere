<?php

namespace App\Repositories\Eloquent;

use App\Models\Invetry;
use App\Repositories\Contracts\InventoryRepositoryInterface;
use App\Support\Tenancy\TenantScope;

class EloquentInventoryRepository implements InventoryRepositoryInterface
{
    public function __construct(private TenantScope $tenantScope)
    {
    }

    public function find($id): ?Invetry
    {
        return $this->tenantScope->apply(Invetry::query())->find($id);
    }

    public function create(array $data): Invetry
    {
        return Invetry::create($data);
    }

    public function updateQuantity(Invetry $item, int $newQuantity): void
    {
        $item->quantity = $newQuantity;
        $item->save();
    }
}
