<?php

namespace App\Services\Product;

use App\Repositories\Contracts\ProductCuttingParamsRepositoryInterface;

/**
 * Split out of ProductConfigService (34 methods, over Sonar's 20-method
 * threshold) - this third backs the "technicalparameters" view's 2 config
 * types: cutting way, CNC sheet thickness.
 */
class ProductCuttingParamsService
{
    public function __construct(private ProductCuttingParamsRepositoryInterface $repository)
    {
    }

    /**
     * Data for the "technicalparameters" view (cutting way / CNC thickness tabs).
     */
    public function getTechnicalParamsViewData($productId): array
    {
        return [
            'Cnsthinks' => $this->repository->getCncThickness($productId),
            'Cutting' => $this->repository->getCuttingWay($productId),
            'id' => $productId,
        ];
    }

    public function createCuttingWay(array $data): void
    {
        $this->repository->createCuttingWay($data);
    }

    public function createCncThickness(array $data): void
    {
        $this->repository->createCncThickness($data);
    }

    public function updateCuttingWay($id, array $data): void
    {
        $this->repository->updateCuttingWay($id, $data);
    }

    public function updateCncThickness($id, array $data): void
    {
        $this->repository->updateCncThickness($id, $data);
    }

    public function deleteCuttingWay($id): void
    {
        $this->repository->deleteCuttingWay($id);
    }

    public function deleteCncThickness($id): void
    {
        $this->repository->deleteCncThickness($id);
    }
}
