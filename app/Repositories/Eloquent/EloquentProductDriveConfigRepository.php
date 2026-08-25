<?php

namespace App\Repositories\Eloquent;

use App\Models\Gear;
use App\Models\Motor;
use App\Models\Rack;
use App\Models\Softerwere1;
use App\Repositories\Contracts\ProductDriveConfigRepositoryInterface;
use App\Support\Tenancy\TenantScope;
use Illuminate\Database\Eloquent\Collection;

class EloquentProductDriveConfigRepository implements ProductDriveConfigRepositoryInterface
{
    public function __construct(private TenantScope $tenantScope)
    {
    }

    public function getMotor($productId): Collection
    {
        return $this->tenantScope->apply(Motor::where('product_id', $productId))->get();
    }

    public function getGear($productId): Collection
    {
        return $this->tenantScope->apply(Gear::where('product_id', $productId))->get();
    }

    public function getRack($productId): Collection
    {
        return $this->tenantScope->apply(Rack::where('product_id', $productId))->get();
    }

    public function getSoftware1($productId): Collection
    {
        return $this->tenantScope->apply(Softerwere1::where('product_id', $productId))->get();
    }

    public function createMotor(array $data): Motor
    {
        return Motor::create($data);
    }

    public function createGear(array $data): Gear
    {
        return Gear::create($data);
    }

    public function createRack(array $data): Rack
    {
        return Rack::create($data);
    }

    public function createSoftware1(array $data): Softerwere1
    {
        return Softerwere1::create($data);
    }

    public function updateMotor($id, array $data): void
    {
        Motor::whereKey($id)->update($data);
    }

    public function updateGear($id, array $data): void
    {
        Gear::whereKey($id)->update($data);
    }

    public function updateRack($id, array $data): void
    {
        Rack::whereKey($id)->update($data);
    }

    public function updateSoftware1($id, array $data): void
    {
        Softerwere1::whereKey($id)->update($data);
    }

    public function deleteMotor($id): void
    {
        Motor::destroy($id);
    }

    public function deleteGear($id): void
    {
        Gear::destroy($id);
    }

    public function deleteRack($id): void
    {
        Rack::destroy($id);
    }

    public function deleteSoftware1($id): void
    {
        Softerwere1::destroy($id);
    }
}
