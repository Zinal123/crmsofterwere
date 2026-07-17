<?php

namespace App\Services\Inventory;

use App\Models\Invetry;
use App\Repositories\Contracts\InventoryRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class InventoryService
{
    public function __construct(
        private InventoryRepositoryInterface $repository,
        private ProductRepositoryInterface $productRepository,
    ) {
    }

    public function getProductList(): Collection
    {
        return $this->productRepository->allOrderedByLatest();
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

        $this->repository->updateQuantity($item, $item->quantity - $requestedQuantity);

        return true;
    }
}
