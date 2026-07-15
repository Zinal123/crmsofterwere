<?php

namespace App\Services\Product;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ProductService
{
    public function __construct(private ProductRepositoryInterface $repository)
    {
    }

    public function list(): Collection
    {
        return $this->repository->allOrderedByLatest();
    }

    public function create(array $data): Product
    {
        return $this->repository->create($data);
    }

    public function delete($id): void
    {
        $this->repository->delete($id);
    }
}
