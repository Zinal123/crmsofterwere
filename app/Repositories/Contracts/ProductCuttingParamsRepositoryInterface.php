<?php

namespace App\Repositories\Contracts;

use App\Models\Cnsthinks;
use App\Models\Cutting;
use Illuminate\Database\Eloquent\Collection;

/**
 * Split out of ProductConfigRepositoryInterface (40 methods, over Sonar's
 * 20-method threshold) - this third covers the "technicalparameters" view's
 * 2 config types: cutting way, CNC sheet thickness.
 */
interface ProductCuttingParamsRepositoryInterface
{
    public function getCuttingWay($productId): Collection;
    public function getCncThickness($productId): Collection;

    public function createCuttingWay(array $data): Cutting;
    public function createCncThickness(array $data): Cnsthinks;

    public function updateCuttingWay($id, array $data): void;
    public function updateCncThickness($id, array $data): void;

    public function deleteCuttingWay($id): void;
    public function deleteCncThickness($id): void;
}
