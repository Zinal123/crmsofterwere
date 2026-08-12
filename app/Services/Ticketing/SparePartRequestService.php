<?php

namespace App\Services\Ticketing;

use App\Models\ClientMachine;
use App\Models\SparePartRequest;
use App\Repositories\Contracts\InventoryRepositoryInterface;
use App\Repositories\Contracts\SparePartRequestRepositoryInterface;
use App\Services\Inventory\AvailabilityService;
use App\Services\Inventory\InventoryService;
use Illuminate\Support\Collection;

class SparePartRequestService
{
    private const STATUSES = ['pending', 'approved', 'fulfilled', 'rejected'];

    public function __construct(
        private SparePartRequestRepositoryInterface $repository,
        private InventoryRepositoryInterface $inventoryRepository,
        private InventoryService $inventoryService,
        private AvailabilityService $availabilityService,
    ) {
    }

    public function listAll(): Collection
    {
        return $this->repository->allWithDetails()->each(function (SparePartRequest $request) {
            $available = $this->availabilityService->available($request->product_id, $request->id);
            $request->is_available = $available >= $request->quantity;
        });
    }

    public function listForClientAccount(int $clientAccountId): Collection
    {
        return $this->repository->forClientAccount($clientAccountId);
    }

    public function find(int $id): SparePartRequest
    {
        $request = $this->repository->find($id);

        if ($request === null) {
            throw new \InvalidArgumentException('Spare part request not found.');
        }

        return $request;
    }

    public function create(ClientMachine $machine, int $productId, int $quantity, ?string $note): SparePartRequest
    {
        return $this->repository->create([
            'client_machine_id' => $machine->id,
            'client_account_id' => $machine->client_account_id,
            'product_id' => $productId,
            'quantity' => max(1, $quantity),
            'note' => $note,
            'status' => 'pending',
        ]);
    }

    public function updateStatus(SparePartRequest $request, string $status): SparePartRequest
    {
        if (! in_array($status, self::STATUSES, true)) {
            throw new \InvalidArgumentException('Invalid status.');
        }

        // When approving, reserve stock — but only if it's actually free.
        // Exclude this request's own claim (it already counts as pending) so a
        // request is never blocked by itself; available then reflects what
        // OTHER pending/approved requests have claimed.
        if ($status === 'approved' && $request->status !== 'approved') {
            $available = $this->availabilityService->available($request->product_id, $request->id);
            if ($request->quantity > $available) {
                throw new \InvalidArgumentException(
                    "Only {$available} available for this part — cannot approve a request for {$request->quantity}."
                );
            }
        }

        if ($status === 'fulfilled' && $request->status !== 'fulfilled') {
            $this->fulfillFromInventory($request);
        }

        $request->status = $status;
        $this->repository->save($request);

        return $request;
    }

    private function fulfillFromInventory(SparePartRequest $request): void
    {
        $inventoryItem = $this->inventoryRepository->allKeyedByProductId()->get($request->product_id);

        if ($inventoryItem === null) {
            throw new \InvalidArgumentException('No inventory record found for this part — cannot fulfill.');
        }

        $this->inventoryService->reduceQuantity($inventoryItem->id, $request->quantity);
    }
}
