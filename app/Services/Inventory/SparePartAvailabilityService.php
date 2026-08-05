<?php

namespace App\Services\Inventory;

use App\Models\SparePartRequest;
use App\Repositories\Contracts\InventoryRepositoryInterface;

class SparePartAvailabilityService
{
    /** Requests in these statuses still hold a claim against on-hand stock. */
    private const RESERVING_STATUSES = ['pending', 'approved'];

    public function __construct(private InventoryRepositoryInterface $inventoryRepository)
    {
    }

    /**
     * Stock currently available for a product: on-hand minus quantity claimed
     * by other pending/approved requests. Fulfilled requests already reduced
     * on-hand directly, so they're not subtracted again here; rejected
     * requests never held a claim.
     */
    public function available(int $productId, ?int $excludingRequestId = null): int
    {
        $onHand = $this->inventoryRepository->allKeyedByProductId()->get($productId)?->quantity ?? 0;

        $reserved = (int) SparePartRequest::query()
            ->where('product_id', $productId)
            ->whereIn('status', self::RESERVING_STATUSES)
            ->when($excludingRequestId, fn ($query) => $query->where('id', '!=', $excludingRequestId))
            ->sum('quantity');

        return max(0, $onHand - $reserved);
    }
}
