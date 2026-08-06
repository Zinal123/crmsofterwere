<?php

namespace App\Services\Inventory;

use App\Models\Invetry;
use App\Repositories\Contracts\InventoryRepositoryInterface;

class InventoryService
{
    public function __construct(private InventoryRepositoryInterface $repository)
    {
    }

    public function create(array $data): Invetry
    {
        return $this->repository->create($data);
    }

    public function reduceQuantity($itemId, $requestedQuantity): bool
    {
        $item = $this->repository->find($itemId);

        if (!$item) {
            return false;
        }

        $newQuantity = $item->quantity - $requestedQuantity;

        if ($newQuantity < 0) {
            throw new \InvalidArgumentException('Cannot reduce quantity below zero (current stock: ' . $item->quantity . ').');
        }

        $this->repository->updateQuantity($item, $newQuantity);

        return true;
    }

    public function setLowStockThreshold(int $itemId, ?int $threshold): bool
    {
        $item = $this->repository->find($itemId);

        if (!$item) {
            return false;
        }

        $item->low_stock_threshold = $threshold;
        $item->save();

        return true;
    }
}
