<?php

namespace App\Services\Inventory;

use App\Models\JobMaterial;
use App\Models\SparePartRequest;
use App\Repositories\Contracts\InventoryRepositoryInterface;

class AvailabilityService
{
    /** Requests in these statuses still hold a claim against on-hand stock. */
    private const RESERVING_STATUSES = ['pending', 'approved'];

    public function __construct(private InventoryRepositoryInterface $inventoryRepository)
    {
    }

    /**
     * Stock currently available for a product: on-hand minus quantity claimed
     * by other pending/approved requests AND by materials on open jobs (a job
     * in progress holds the parts it needs). Fulfilled requests already reduced
     * on-hand directly, so they're not subtracted again here; rejected requests
     * and completed/rejected jobs never hold a claim. Pass $excludingRequestId
     * or $excludingJobId to omit a specific request's / job's own demand.
     */
    public function available(int $productId, ?int $excludingRequestId = null, ?int $excludingJobId = null): int
    {
        $onHand = $this->inventoryRepository->allKeyedByProductId()->get($productId)?->quantity ?? 0;

        $reservedByRequests = (int) SparePartRequest::query()
            ->where('product_id', $productId)
            ->whereIn('status', self::RESERVING_STATUSES)
            ->when($excludingRequestId, fn ($q) => $q->where('id', '!=', $excludingRequestId))
            ->sum('quantity');

        $reservedByJobs = (int) JobMaterial::query()
            ->where('product_id', $productId)
            ->whereHas('job', fn ($q) => $q->open())
            ->when($excludingJobId, fn ($q) => $q->where('job_id', '!=', $excludingJobId))
            ->sum('quantity');

        return max(0, $onHand - $reservedByRequests - $reservedByJobs);
    }
}
