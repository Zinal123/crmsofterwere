<?php

namespace App\Services\Quotation;

use App\Models\Quation;
use App\Repositories\Contracts\ProductConfigRepositoryInterface;
use App\Repositories\Contracts\QuotationRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

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

    /**
     * $items is an array of ['description' => ..., 'amount' => ...] pairs -
     * as many as the pricing table's "Add Row" button produced client-side,
     * not capped at the 3 legacy description/amount/description1/... columns.
     */
    public function create(array $data, array $items = []): Quation
    {
        return DB::transaction(function () use ($data, $items) {
            $quotation = $this->repository->create($data);

            foreach ($items as $item) {
                if (blank($item['description'] ?? null) && blank($item['amount'] ?? null)) {
                    continue;
                }

                $this->repository->createItem($quotation->id, $item);
            }

            return $quotation;
        });
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
