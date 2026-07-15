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
}
