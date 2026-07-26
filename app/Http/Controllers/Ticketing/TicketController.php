<?php

namespace App\Http\Controllers\Ticketing;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\Ticketing\TicketService;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function __construct(
        private TicketService $service,
        private UserRepositoryInterface $userRepository,
    ) {
    }

    public function index()
    {
        return view('ticketing.tickets.index', [
            'tickets' => $this->service->listAll(),
        ]);
    }

    public function show($id)
    {
        return view('ticketing.tickets.show', [
            'ticket' => $this->service->find((int) $id),
            'workers' => $this->userRepository->byRole('Worker'),
        ]);
    }

    public function assign(Request $request, $id)
    {
        $request->validate([
            'worker_id' => 'required|integer|exists:users,id',
        ]);

        $ticket = $this->service->find((int) $id);

        try {
            $this->service->assign($ticket, $request->user(), (int) $request->input('worker_id'));
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('admin.tickets.show', $id)->with('error', $e->getMessage());
        }

        return redirect()->route('admin.tickets.show', $id)->with('success', 'Ticket assigned - a job has been created for the technician.');
    }
}
