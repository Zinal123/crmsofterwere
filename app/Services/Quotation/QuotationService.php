<?php

namespace App\Services\Quotation;

use App\Models\Quation;
use App\Repositories\Contracts\ProductConfigRepositoryInterface;
use App\Repositories\Contracts\QuotationRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class QuotationService
{
    public function __construct(
        private QuotationRepositoryInterface $repository,
        private ProductConfigRepositoryInterface $productConfigRepository,
    ) {
    }

    public function list(): Collection
    {
        return $this->repository->allOrderedByLatest();
    }

    public function create(array $data): Quation
    {
        return $this->repository->create($data);
    }

    /**
     * Fixed 2026-07-19: previously hardcoded $productId = 1 regardless of
     * the caller's route {id} - both the fiber and CO2 quotation forms
     * always showed product_id=1's config data no matter which product
     * the quotation was actually for. Now uses the real product ID.
     */
    public function getQuotationFormViewData($productId): array
    {
        return [
            'softeredetails' => $this->productConfigRepository->getSoftware($productId),
            'lasercutting' => $this->productConfigRepository->getLaserCutting($productId),
            'fource' => $this->productConfigRepository->getFocusing($productId),
            'power' => $this->productConfigRepository->getPower($productId),
            'cutting' => $this->productConfigRepository->getCuttingWay($productId),
            'cnsthinks' => $this->productConfigRepository->getCncThickness($productId),
            'motor' => $this->productConfigRepository->getMotor($productId),
            'gear' => $this->productConfigRepository->getGear($productId),
            'rack' => $this->productConfigRepository->getRack($productId),
            'softere' => $this->productConfigRepository->getSoftware1($productId),
        ];
    }
}
