<?php

namespace App\Services\Product;

use App\Repositories\Contracts\ProductDriveConfigRepositoryInterface;

/**
 * Split out of ProductConfigService (34 methods, over Sonar's 20-method
 * threshold) - this third backs the "standerconfiglist" view's 4 config
 * types: motor, gear, rack, nesting software.
 */
class ProductDriveConfigService
{
    public function __construct(private ProductDriveConfigRepositoryInterface $repository)
    {
    }

    /**
     * Data for the "standerconfiglist" view (motor / gear / rack / software tabs).
     */
    public function getStanderconfigListViewData($productId): array
    {
        return [
            'Motor' => $this->repository->getMotor($productId),
            'Gear' => $this->repository->getGear($productId),
            'Rack' => $this->repository->getRack($productId),
            'Softerwere1' => $this->repository->getSoftware1($productId),
            'id' => $productId,
        ];
    }

    public function createMotor(array $data): void
    {
        $this->repository->createMotor($data);
    }

    public function createGear(array $data): void
    {
        $this->repository->createGear($data);
    }

    public function createRack(array $data): void
    {
        $this->repository->createRack($data);
    }

    public function createSoftware1(array $data): void
    {
        $this->repository->createSoftware1($data);
    }

    public function updateMotor($id, array $data): void
    {
        $this->repository->updateMotor($id, $data);
    }

    public function updateGear($id, array $data): void
    {
        $this->repository->updateGear($id, $data);
    }

    public function updateRack($id, array $data): void
    {
        $this->repository->updateRack($id, $data);
    }

    public function updateSoftware1($id, array $data): void
    {
        $this->repository->updateSoftware1($id, $data);
    }

    public function deleteMotor($id): void
    {
        $this->repository->deleteMotor($id);
    }

    public function deleteGear($id): void
    {
        $this->repository->deleteGear($id);
    }

    public function deleteRack($id): void
    {
        $this->repository->deleteRack($id);
    }

    public function deleteSoftware1($id): void
    {
        $this->repository->deleteSoftware1($id);
    }
}
