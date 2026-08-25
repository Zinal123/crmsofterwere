<?php

namespace App\Repositories\Contracts;

use App\Models\Fource;
use App\Models\Lasercutting;
use App\Models\Power;
use App\Models\Softerwere;
use Illuminate\Database\Eloquent\Collection;

/**
 * Split out of ProductConfigRepositoryInterface (40 methods, over Sonar's
 * 20-method threshold) - this third covers the "standerconfig" view's 4
 * config types: software, laser cutting head, focusing, power source.
 */
interface ProductOpticalConfigRepositoryInterface
{
    public function getSoftware($productId): Collection;
    public function getLaserCutting($productId): Collection;
    public function getFocusing($productId): Collection;
    public function getPower($productId): Collection;

    public function createSoftware(array $data): Softerwere;
    public function createLaserCutting(array $data): Lasercutting;
    public function createFocusing(array $data): Fource;
    public function createPower(array $data): Power;

    public function updateSoftware($id, array $data): void;
    public function updateLaserCutting($id, array $data): void;
    public function updateFocusing($id, array $data): void;
    public function updatePower($id, array $data): void;

    public function deleteSoftware($id): void;
    public function deleteLaserCutting($id): void;
    public function deleteFocusing($id): void;
    public function deletePower($id): void;
}
