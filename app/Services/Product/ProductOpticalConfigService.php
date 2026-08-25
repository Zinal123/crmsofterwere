<?php

namespace App\Services\Product;

use App\Repositories\Contracts\ProductOpticalConfigRepositoryInterface;

/**
 * Split out of ProductConfigService (34 methods, over Sonar's 20-method
 * threshold) - this third backs the "standerconfig" view's 4 config types:
 * software, laser cutting head, focusing, power source.
 */
class ProductOpticalConfigService
{
    public function __construct(private ProductOpticalConfigRepositoryInterface $repository)
    {
    }

    /**
     * Data for the "standerconfig" view (software / laser cutting / focusing / power tabs).
     */
    public function getConfigViewData($productId): array
    {
        return [
            'softwere' => $this->repository->getSoftware($productId),
            'Lasercutting' => $this->repository->getLaserCutting($productId),
            'Focusing' => $this->repository->getFocusing($productId),
            'power' => $this->repository->getPower($productId),
            'id' => $productId,
        ];
    }

    public function createSoftware(array $data): void
    {
        $this->repository->createSoftware($data);
    }

    public function createLaserCutting(array $data): void
    {
        $this->repository->createLaserCutting($data);
    }

    public function createFocusing(array $data): void
    {
        $this->repository->createFocusing($data);
    }

    public function createPower(array $data): void
    {
        $this->repository->createPower($data);
    }

    public function updateSoftware($id, array $data): void
    {
        $this->repository->updateSoftware($id, $data);
    }

    public function updateLaserCutting($id, array $data): void
    {
        $this->repository->updateLaserCutting($id, $data);
    }

    public function updateFocusing($id, array $data): void
    {
        $this->repository->updateFocusing($id, $data);
    }

    public function updatePower($id, array $data): void
    {
        $this->repository->updatePower($id, $data);
    }

    public function deleteSoftware($id): void
    {
        $this->repository->deleteSoftware($id);
    }

    public function deleteLaserCutting($id): void
    {
        $this->repository->deleteLaserCutting($id);
    }

    public function deleteFocusing($id): void
    {
        $this->repository->deleteFocusing($id);
    }

    public function deletePower($id): void
    {
        $this->repository->deletePower($id);
    }
}
