<?php

namespace App\Repositories\Contracts;

use App\Models\Gear;
use App\Models\Motor;
use App\Models\Rack;
use App\Models\Softerwere1;
use Illuminate\Database\Eloquent\Collection;

/**
 * Split out of ProductConfigRepositoryInterface (40 methods, over Sonar's
 * 20-method threshold) - this third covers the "standerconfiglist" view's 4
 * config types: motor, gear, rack, nesting software.
 */
interface ProductDriveConfigRepositoryInterface
{
    public function getMotor($productId): Collection;
    public function getGear($productId): Collection;
    public function getRack($productId): Collection;
    public function getSoftware1($productId): Collection;

    public function createMotor(array $data): Motor;
    public function createGear(array $data): Gear;
    public function createRack(array $data): Rack;
    public function createSoftware1(array $data): Softerwere1;

    public function updateMotor($id, array $data): void;
    public function updateGear($id, array $data): void;
    public function updateRack($id, array $data): void;
    public function updateSoftware1($id, array $data): void;

    public function deleteMotor($id): void;
    public function deleteGear($id): void;
    public function deleteRack($id): void;
    public function deleteSoftware1($id): void;
}
