<?php

namespace App\Repositories\Eloquent;

use App\Models\Vendor;
use App\Repositories\Contracts\VendorRepositoryInterface;
use App\Support\Tenancy\TenantScope;
use Illuminate\Support\Collection;

class EloquentVendorRepository implements VendorRepositoryInterface
{
    public function __construct(private TenantScope $tenantScope)
    {
    }

    public function allOrderedByLatest(): Collection
    {
        return $this->tenantScope->apply(Vendor::query())->orderByDesc('id')->get();
    }

    public function find(int $id): ?Vendor
    {
        return Vendor::find($id);
    }

    public function create(array $data): Vendor
    {
        return Vendor::create($data);
    }

    public function update(Vendor $vendor, array $data): Vendor
    {
        $vendor->update($data);

        return $vendor;
    }

    public function delete(Vendor $vendor): void
    {
        $vendor->delete();
    }
}
