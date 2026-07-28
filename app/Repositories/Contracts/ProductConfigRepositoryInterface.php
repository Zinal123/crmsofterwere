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

    public function updateSoftware($id, array $data): void;
    public function updateLaserCutting($id, array $data): void;
    public function updateFocusing($id, array $data): void;
    public function updatePower($id, array $data): void;
    public function updateMotor($id, array $data): void;
    public function updateGear($id, array $data): void;
    public function updateRack($id, array $data): void;
    public function updateSoftware1($id, array $data): void;
    public function updateCuttingWay($id, array $data): void;
    public function updateCncThickness($id, array $data): void;

    public function deleteSoftware($id): void;
    public function deleteLaserCutting($id): void;
    public function deleteFocusing($id): void;
    public function deletePower($id): void;
    public function deleteMotor($id): void;
    public function deleteGear($id): void;
    public function deleteRack($id): void;
    public function deleteSoftware1($id): void;
    public function deleteCuttingWay($id): void;
    public function deleteCncThickness($id): void;
}
