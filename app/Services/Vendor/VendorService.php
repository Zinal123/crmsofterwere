<?php

namespace App\Services\Vendor;

use App\Models\Vendor;
use App\Repositories\Contracts\VendorRepositoryInterface;
use Illuminate\Support\Collection;

class VendorService
{
    public function __construct(private VendorRepositoryInterface $repository)
    {
    }

    public function listAll(): Collection
    {
        return $this->repository->allOrderedByLatest();
    }

    public function find(int $id): ?Vendor
    {
        return $this->repository->find($id);
    }

    public function create(array $data): Vendor
    {
        return $this->repository->create([
            'name' => $data['name'],
            'category' => $data['category'],
            'gstin' => $data['gstin'] ?? null,
            'contact_name' => $data['contact_name'] ?? null,
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'state' => $data['state'] ?? null,
            'country' => $data['country'] ?? 'India',
            'notes' => $data['notes'] ?? null,
            'is_active' => true,
        ]);
    }

    public function update(Vendor $vendor, array $data): Vendor
    {
        return $this->repository->update($vendor, [
            'name' => $data['name'],
            'category' => $data['category'],
            'gstin' => $data['gstin'] ?? null,
            'contact_name' => $data['contact_name'] ?? null,
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'state' => $data['state'] ?? null,
            'country' => $data['country'] ?? 'India',
            'notes' => $data['notes'] ?? null,
        ]);
    }

    public function delete(Vendor $vendor): void
    {
        $this->repository->delete($vendor);
    }
}
