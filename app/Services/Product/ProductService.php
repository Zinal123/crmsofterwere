<?php

namespace App\Services\Product;

use App\Models\Product;
use App\Repositories\Contracts\InventoryRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ProductService
{
    public function __construct(
        private ProductRepositoryInterface $repository,
        private InventoryRepositoryInterface $inventoryRepository,
    ) {
    }

    public function list(): Collection
    {
        return $this->repository->allOrderedByLatest();
    }

    /**
     * Products with their linked inventory (stock quantity/vendor) attached
     * as ->inventory, or null for a product that has no stock tracked yet.
     */
    public function listWithInventory(): Collection
    {
        $products = $this->repository->allOrderedByLatest();
        $inventoryByProductId = $this->inventoryRepository->allKeyedByProductId();

        return $products->each(function (Product $product) use ($inventoryByProductId) {
            $product->inventory = $inventoryByProductId->get($product->id);
        });
    }

    public function create(array $data): Product
    {
        return $this->repository->create($data);
    }

    /**
     * Creates a product and, only if stock info was actually given (either
     * field non-empty), an initial inventory row linked to it in the same
     * transaction. A product with neither field filled in is created with
     * no inventory row at all - stock tracking stays opt-in per product.
     */
    public function createWithInventory(array $data): Product
    {
        return DB::transaction(function () use ($data) {
            $product = $this->repository->create($data);

            if (!empty($data['quantity']) || !empty($data['vandername'])) {
                $this->inventoryRepository->create([
                    'product_id' => $product->id,
                    'quantity' => $data['quantity'] ?? 0,
                    'vandername' => $data['vandername'] ?? null,
                    'rate' => $data['rate'] ?? null,
                ]);
            }

            return $product;
        });
    }

    public function delete($id): void
    {
        $this->repository->delete($id);
    }
}
