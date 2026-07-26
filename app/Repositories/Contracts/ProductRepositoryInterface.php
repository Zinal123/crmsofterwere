<?php

namespace App\Repositories\Contracts;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

interface ProductRepositoryInterface
{
    public function allOrderedByLatest(): Collection;

    public function find($id): ?Product;

    public function create(array $data): Product;

    public function delete($id): void;

    public function allSpareParts(): Collection;

    public function save(Product $product): void;
}
