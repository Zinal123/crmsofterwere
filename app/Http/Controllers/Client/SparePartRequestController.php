<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\ClientMachineRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Services\Ticketing\SparePartRequestService;
use Illuminate\Http\Request;

class SparePartRequestController extends Controller
{
    public function __construct(
        private SparePartRequestService $service,
        private ClientMachineRepositoryInterface $machineRepository,
        private ProductRepositoryInterface $productRepository,
    ) {
    }

    public function index(Request $request)
    {
        return view('client.spare-parts.index', [
            'requests' => $this->service->listForClientAccount($request->user('client')->id),
        ]);
    }

    public function create(Request $request, $machineId)
    {
        $machine = $this->machineRepository->find((int) $machineId);

        if ($machine === null) {
            abort(404);
        }

        $this->authorizeOwnership($request, $machine->client_account_id);

        return view('client.spare-parts.create', [
            'machine' => $machine,
            'spareParts' => $this->productRepository->allSpareParts(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'client_machine_id' => 'required|integer',
            'product_id' => 'required|integer|exists:product,id',
            'quantity' => 'required|integer|min:1|max:100',
            'note' => 'nullable|string|max:1000',
        ]);

        $machine = $this->machineRepository->find((int) $data['client_machine_id']);

        if ($machine === null) {
            abort(404);
        }

        $this->authorizeOwnership($request, $machine->client_account_id);

        $sparePartRequest = $this->service->create($machine, (int) $data['product_id'], (int) $data['quantity'], $data['note'] ?? null);

        return redirect()->route('client.spare-parts.index')->with('success', 'Your spare part request has been submitted (request #' . $sparePartRequest->id . ').');
    }

    private function authorizeOwnership(Request $request, int $ownerClientAccountId): void
    {
        if ($request->user('client')->id !== $ownerClientAccountId) {
            abort(403);
        }
    }
}
