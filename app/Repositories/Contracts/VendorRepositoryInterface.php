<?php

namespace App\Repositories\Contracts;

use App\Models\Vendor;
use Illuminate\Support\Collection;

interface VendorRepositoryInterface
{
    public function allOrderedByLatest(): Collection;

    public function find(int $id): ?Vendor;

    public function create(array $data): Vendor;

    public function update(Vendor $vendor, array $data): Vendor;

    public function delete(Vendor $vendor): void;
}
