<?php

namespace App\Repositories\Eloquent;

use App\Models\Cnsthinks;
use App\Models\Cutting;
use App\Repositories\Contracts\ProductCuttingParamsRepositoryInterface;
use App\Support\Tenancy\TenantScope;
use Illuminate\Database\Eloquent\Collection;

class EloquentProductCuttingParamsRepository implements ProductCuttingParamsRepositoryInterface
{
    public function __construct(private TenantScope $tenantScope)
    {
    }

    public function getCuttingWay($productId): Collection
    {
        return $this->tenantScope->apply(Cutting::where('product_id', $productId))->get();
    }

    public function getCncThickness($productId): Collection
    {
        return $this->tenantScope->apply(Cnsthinks::where('product_id', $productId))->get();
    }

    public function createCuttingWay(array $data): Cutting
    {
        return Cutting::create($data);
    }

    public function createCncThickness(array $data): Cnsthinks
    {
        return Cnsthinks::create($data);
    }

    public function updateCuttingWay($id, array $data): void
    {
        Cutting::whereKey($id)->update($data);
    }

    public function updateCncThickness($id, array $data): void
    {
        Cnsthinks::whereKey($id)->update($data);
    }

    public function deleteCuttingWay($id): void
    {
        Cutting::destroy($id);
    }

    public function deleteCncThickness($id): void
    {
        Cnsthinks::destroy($id);
    }
}
