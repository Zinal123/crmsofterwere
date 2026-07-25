<?php

namespace App\Repositories\Contracts;

use App\Models\Invetry;
use Illuminate\Database\Eloquent\Collection;

interface InventoryRepositoryInterface
{
    public function find($id): ?Invetry;

    public function create(array $data): Invetry;

    public function updateQuantity(Invetry $item, int $newQuantity): void;

    /**
     * All inventory rows keyed by product_id, for merging stock data onto
     * the product list. Rows with no product_id (orphaned data) are excluded.
     */
    public function allKeyedByProductId(): Collection;
}
