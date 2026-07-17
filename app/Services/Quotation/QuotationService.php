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
     * Pre-existing bug, preserved: the quotation form (fiber and CO2 both)
     * always shows product_id=1's config data, regardless of the route's
     * {id} - the original inline-PHP view code this replaces hardcoded
     * `$product = 1` and never read the route parameter either.
     */
    public function getQuotationFormViewData(): array
    {
        $productId = 1;

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
