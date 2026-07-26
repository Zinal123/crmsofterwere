<?php

namespace App\Services\Ticketing;

use App\Models\ClientMachine;
use App\Models\SparePartRequest;
use App\Repositories\Contracts\SparePartRequestRepositoryInterface;
use Illuminate\Support\Collection;

class SparePartRequestService
{
    private const STATUSES = ['pending', 'approved', 'fulfilled', 'rejected'];

    public function __construct(private SparePartRequestRepositoryInterface $repository)
    {
    }

    public function listAll(): Collection
    {
        return $this->repository->allWithDetails();
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

        $request->status = $status;
        $this->repository->save($request);

        return $request;
    }
}
