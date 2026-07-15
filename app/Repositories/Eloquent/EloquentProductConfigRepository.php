<?php

namespace App\Repositories\Eloquent;

use App\Models\Cnsthinks;
use App\Models\Cutting;
use App\Models\Fource;
use App\Models\Gear;
use App\Models\Lasercutting;
use App\Models\Motor;
use App\Models\Power;
use App\Models\Rack;
use App\Models\Softerwere;
use App\Models\Softerwere1;
use App\Repositories\Contracts\ProductConfigRepositoryInterface;
use App\Support\Tenancy\TenantScope;
use Illuminate\Database\Eloquent\Collection;

class EloquentProductConfigRepository implements ProductConfigRepositoryInterface
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

    public function getCuttingWay($productId): Collection
    {
        return $this->tenantScope->apply(Cutting::where('product_id', $productId))->get();
    }

    public function getCncThickness($productId): Collection
    {
        return $this->tenantScope->apply(Cnsthinks::where('product_id', $productId))->get();
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

    public function createCuttingWay(array $data): Cutting
    {
        return Cutting::create($data);
    }

    public function createCncThickness(array $data): Cnsthinks
    {
        return Cnsthinks::create($data);
    }
}
