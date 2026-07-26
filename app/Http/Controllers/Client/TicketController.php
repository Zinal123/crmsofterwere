<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\ClientMachineRepositoryInterface;
use App\Services\Ticketing\TicketProblemTypeService;
use App\Services\Ticketing\TicketService;
use App\Support\ImageCompressor;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function __construct(
        private TicketService $service,
        private ClientMachineRepositoryInterface $machineRepository,
        private TicketProblemTypeService $problemTypeService,
        private ImageCompressor $compressor,
    ) {
    }

    public function index(Request $request)
    {
        return view('client.tickets.index', [
            'tickets' => $this->service->listForClientAccount($request->user('client')->id),
        ]);
    }

    public function show(Request $request, $id)
    {
        $ticket = $this->service->find((int) $id);
        $this->authorizeOwnership($request, $ticket->client_account_id);

        return view('client.tickets.show', ['ticket' => $ticket]);
    }

    public function create(Request $request, $machineId)
    {
        $machine = $this->machineRepository->find((int) $machineId);

        if ($machine === null) {
            abort(404);
        }

        $this->authorizeOwnership($request, $machine->client_account_id);

        return view('client.tickets.create', [
            'machine' => $machine,
            'problemTypes' => $this->problemTypeService->listActive(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'client_machine_id' => 'required|integer',
            'problem_type_id' => 'required|integer|exists:ticket_problem_types,id',
            'description' => 'nullable|string|max:2000',
            'photos' => 'nullable|array|max:5',
            'photos.*' => 'image|max:5120',
        ]);

        $machine = $this->machineRepository->find((int) $data['client_machine_id']);

        if ($machine === null) {
            abort(404);
        }

        $this->authorizeOwnership($request, $machine->client_account_id);

        $photoPaths = [];
        foreach ($request->file('photos', []) as $file) {
            $path = 'ticket-photos/' . $machine->client_account_id . '/' . uniqid('photo_', true) . '.jpg';
            $this->compressor->compress($file, $path);
            $photoPaths[] = $path;
        }

        $ticket = $this->service->raise($machine, (int) $data['problem_type_id'], $data['description'] ?? null, $photoPaths);

        return redirect()->route('client.tickets.show', $ticket->id)->with('success', 'Your ticket has been raised.');
    }

    private function authorizeOwnership(Request $request, int $ownerClientAccountId): void
    {
        if ($request->user('client')->id !== $ownerClientAccountId) {
            abort(403);
        }
    }
}
