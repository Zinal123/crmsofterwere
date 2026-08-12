<?php

namespace App\Services\Quotation;

use App\Models\Quation;
use App\Repositories\Contracts\BankRepositoryInterface;
use App\Repositories\Contracts\ProductConfigRepositoryInterface;
use App\Repositories\Contracts\QuotationRepositoryInterface;
use App\Services\Inventory\AvailabilityService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class QuotationService
{
    public function __construct(
        private QuotationRepositoryInterface $repository,
        private ProductConfigRepositoryInterface $productConfigRepository,
        private BankRepositoryInterface $bankRepository,
        private AvailabilityService $availabilityService,
    ) {
    }

    public function list(): Collection
    {
        return $this->repository->allOrderedByLatest();
    }

    public function delete(int $id): void
    {
        $this->repository->delete($id);
    }

    /**
     * Only the client/contact-facing fields are editable here - the
     * technical machine-spec fields (cutting/power/motor/etc.) are set once
     * at generation time via the dedicated fiber/CO2 forms, not corrected
     * after the fact through this simpler edit.
     */
    public function update(int $id, array $data): Quation
    {
        $quotation = $this->repository->find($id);

        if ($quotation === null) {
            throw new \InvalidArgumentException('Quotation not found.');
        }

        $quotation->fill($data);
        $this->repository->save($quotation);

        return $quotation;
    }

    /**
     * Resolves every dropdown selection stored on the quotation (just IDs)
     * back to its real config record, so the PDF can show what was actually
     * picked instead of the hardcoded, unrelated content it used to ship
     * with. Falls back to product_id 1 (the one real Fiber machine) only
     * when the quotation has no product_id at all - legacy rows saved
     * before the create-form's hidden product_id field was ever populated.
     */
    public function getQuotationPdfData(int $id): array
    {
        $quotation = $this->repository->findWithDetails($id);
        $productId = $quotation->product_id ?: 1;

        $quotation->items->each(function ($item) {
            if ($item->product_id !== null) {
                $item->is_available = $this->availabilityService->available($item->product_id) >= ($item->quantity ?? 1);
            }
        });

        return [
            'quotation' => $quotation,
            'bank' => $quotation->bank ? $this->bankRepository->find($quotation->bank) : null,
            'software' => $this->productConfigRepository->getSoftware($productId)->firstWhere('id', $quotation->softweredetails),
            'lasercuttingMachine' => $this->productConfigRepository->getLaserCutting($productId)->firstWhere('id', $quotation->lasercutting),
            'focus' => $this->productConfigRepository->getFocusing($productId)->firstWhere('id', $quotation->focus),
            'power' => $this->productConfigRepository->getPower($productId)->firstWhere('id', $quotation->power),
            'cuttingWay' => $this->productConfigRepository->getCuttingWay($productId)->firstWhere('id', $quotation->cuttingway),
            'cuttingThickness' => $this->productConfigRepository->getCncThickness($productId)->firstWhere('id', $quotation->cuttingthickess),
            'motor' => $this->productConfigRepository->getMotor($productId)->firstWhere('id', $quotation->motor),
            'motorType' => $this->productConfigRepository->getMotor($productId)->firstWhere('id', $quotation->motortype),
            'gear' => $this->productConfigRepository->getGear($productId)->firstWhere('id', $quotation->gearbox),
            'rack' => $this->productConfigRepository->getRack($productId)->firstWhere('id', $quotation->rack),
            'software1' => $this->productConfigRepository->getSoftware1($productId)->firstWhere('id', $quotation->software),
        ];
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
            'bank' => $this->bankRepository->all(),
        ];
    }
}
