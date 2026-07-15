<?php

namespace App\Repositories\Contracts;

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
use Illuminate\Database\Eloquent\Collection;

interface ProductConfigRepositoryInterface
{
    public function getSoftware($productId): Collection;
    public function getLaserCutting($productId): Collection;
    public function getFocusing($productId): Collection;
    public function getPower($productId): Collection;
    public function getMotor($productId): Collection;
    public function getGear($productId): Collection;
    public function getRack($productId): Collection;
    public function getSoftware1($productId): Collection;
    public function getCuttingWay($productId): Collection;
    public function getCncThickness($productId): Collection;

    public function createSoftware(array $data): Softerwere;
    public function createLaserCutting(array $data): Lasercutting;
    public function createFocusing(array $data): Fource;
    public function createPower(array $data): Power;
    public function createMotor(array $data): Motor;
    public function createGear(array $data): Gear;
    public function createRack(array $data): Rack;
    public function createSoftware1(array $data): Softerwere1;
    public function createCuttingWay(array $data): Cutting;
    public function createCncThickness(array $data): Cnsthinks;
}
