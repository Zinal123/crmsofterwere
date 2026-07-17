<?php

namespace App\Repositories\Contracts;

use App\Models\Invetry;

interface InventoryRepositoryInterface
{
    public function find($id): ?Invetry;

    public function create(array $data): Invetry;

    public function updateQuantity(Invetry $item, int $newQuantity): void;
}
