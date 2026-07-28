<?php

namespace App\Services\Product;

use App\Repositories\Contracts\ProductConfigRepositoryInterface;

class ProductConfigService
{
    public function __construct(private ProductConfigRepositoryInterface $repository)
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

    public function createCuttingWay(array $data): void
    {
        $this->repository->createCuttingWay($data);
    }

    public function createCncThickness(array $data): void
    {
        $this->repository->createCncThickness($data);
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

    public function updateCuttingWay($id, array $data): void
    {
        $this->repository->updateCuttingWay($id, $data);
    }

    public function updateCncThickness($id, array $data): void
    {
        $this->repository->updateCncThickness($id, $data);
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

    public function deleteCuttingWay($id): void
    {
        $this->repository->deleteCuttingWay($id);
    }

    public function deleteCncThickness($id): void
    {
        $this->repository->deleteCncThickness($id);
    }
}
