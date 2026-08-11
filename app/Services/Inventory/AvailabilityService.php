<?php

namespace App\Services\Inventory;

use App\Models\Invetry;
use App\Models\SparePartRequest;
use Illuminate\Support\Collection;

class AvailabilityService
{
    /** Statuses of a spare-part request that hold (reserve) stock. */
    private const RESERVING_STATUSES = ['approved'];

    public function onHand(int $productId): int
    {
        return (int) Invetry::where('product_id', $productId)->sum('quantity');
    }

    public function reserved(int $productId): int
    {
        return (int) SparePartRequest::where('product_id', $productId)
            ->whereIn('status', self::RESERVING_STATUSES)
            ->sum('quantity');
    }

    public function available(int $productId): int
    {
        return $this->onHand($productId) - $this->reserved($productId);
    }

    /** On-hand/reserved/available for every product that has an inventory row. */
    public function snapshot(): Collection
    {
        $onHand = Invetry::query()
            ->selectRaw('product_id, SUM(quantity) as qty')
            ->groupBy('product_id')
            ->pluck('qty', 'product_id');

        $reserved = SparePartRequest::query()
            ->whereIn('status', self::RESERVING_STATUSES)
            ->selectRaw('product_id, SUM(quantity) as qty')
            ->groupBy('product_id')
            ->pluck('qty', 'product_id');

        return $onHand->keys()->mapWithKeys(function ($productId) use ($onHand, $reserved) {
            $hand = (int) $onHand[$productId];
            $res = (int) ($reserved[$productId] ?? 0);

            return [$productId => ['on_hand' => $hand, 'reserved' => $res, 'available' => $hand - $res]];
        });
    }
}
