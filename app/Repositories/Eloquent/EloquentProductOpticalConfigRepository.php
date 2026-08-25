<?php

namespace App\Repositories\Eloquent;

use App\Models\Fource;
use App\Models\Lasercutting;
use App\Models\Power;
use App\Models\Softerwere;
use App\Repositories\Contracts\ProductOpticalConfigRepositoryInterface;
use App\Support\Tenancy\TenantScope;
use Illuminate\Database\Eloquent\Collection;

class EloquentProductOpticalConfigRepository implements ProductOpticalConfigRepositoryInterface
{
    public function __construct(private TenantScope $tenantScope)
    {
    }

    public function getSoftware($productId): Collection
    {
        return $this->tenantScope->apply(Softerwere::where('product_id', $productId))->get();
    }

    public function getLaserCutting($productId): Collection
    {
        return $this->tenantScope->apply(Lasercutting::where('product_id', $productId))->get();
    }

    public function getFocusing($productId): Collection
    {
        return $this->tenantScope->apply(Fource::where('product_id', $productId))->get();
    }

    public function getPower($productId): Collection
    {
        return $this->tenantScope->apply(Power::where('product_id', $productId))->get();
    }

    public function createSoftware(array $data): Softerwere
    {
        return Softerwere::create($data);
    }

    public function createLaserCutting(array $data): Lasercutting
    {
        return Lasercutting::create($data);
    }

    public function createFocusing(array $data): Fource
    {
        return Fource::create($data);
    }

    public function createPower(array $data): Power
    {
        return Power::create($data);
    }

    public function updateSoftware($id, array $data): void
    {
        Softerwere::whereKey($id)->update($data);
    }

    public function updateLaserCutting($id, array $data): void
    {
        Lasercutting::whereKey($id)->update($data);
    }

    public function updateFocusing($id, array $data): void
    {
        Fource::whereKey($id)->update($data);
    }

    public function updatePower($id, array $data): void
    {
        Power::whereKey($id)->update($data);
    }

    public function deleteSoftware($id): void
    {
        Softerwere::destroy($id);
    }

    public function deleteLaserCutting($id): void
    {
        Lasercutting::destroy($id);
    }

    public function deleteFocusing($id): void
    {
        Fource::destroy($id);
    }

    public function deletePower($id): void
    {
        Power::destroy($id);
    }
}
