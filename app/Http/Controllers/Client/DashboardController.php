<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\ClientMachineRepositoryInterface;
use App\Repositories\Contracts\SparePartRequestRepositoryInterface;
use App\Repositories\Contracts\TicketRepositoryInterface;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private ClientMachineRepositoryInterface $machineRepository,
        private TicketRepositoryInterface $ticketRepository,
        private SparePartRequestRepositoryInterface $sparePartRepository,
    ) {
    }

    public function index(Request $request)
    {
        $clientId = $request->user('client')->id;
        $machines = $this->machineRepository->forClientAccount($clientId);
        $tickets = $this->ticketRepository->forClientAccount($clientId);
        $spareParts = $this->sparePartRepository->forClientAccount($clientId);

        return view('client.dashboard', [
            'machines' => $machines,
            // Tickets not yet resolved, and spare-part requests still in flight.
            'openTickets' => $tickets->where('status', '!=', 'resolved')->count(),
            'openSpareParts' => $spareParts->whereIn('status', ['pending', 'approved'])->count(),
            'amountDue' => $machines->sum(fn ($machine) => (float) ($machine->invoice->remaining_amount ?? 0)),
        ]);
    }
}
