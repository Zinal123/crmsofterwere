<?php

namespace App\Repositories\Eloquent;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Support\Tenancy\TenantScope;
use Illuminate\Database\Eloquent\Collection;

class EloquentProductRepository implements ProductRepositoryInterface
{
    public function __construct(private TenantScope $tenantScope)
    {
    }

    public function allOrderedByLatest(): Collection
    {
        return $this->tenantScope->apply(Product::orderBy('id', 'desc'))->get();
    }

    public function create(array $data): Product
    {
        return Product::create($data);
    }

    public function delete($id): void
    {
        // PHP class names are case-insensitive, so the pre-existing code's
        // lowercase `product::find()` resolved to this same class with
        // identical behavior — using the correct casing here changes
        // nothing observable, just cleans up the reference.
        Product::find($id)->delete();
    }
}
