<?php

namespace App\Services\Ticketing;

use App\Models\ClientMachine;
use App\Models\Job;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketStatusChangedNotification;
use App\Repositories\Contracts\TicketRepositoryInterface;
use App\Services\Job\JobService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TicketService
{
    public function __construct(
        private TicketRepositoryInterface $repository,
        private JobService $jobService,
    ) {
    }

    public function listAll(): Collection
    {
        return $this->repository->allWithDetails();
    }

    public function listForClientAccount(int $clientAccountId): Collection
    {
        return $this->repository->forClientAccount($clientAccountId);
    }

    public function find(int $id): Ticket
    {
        $ticket = $this->repository->find($id);

        if ($ticket === null) {
            throw new \InvalidArgumentException('Ticket not found.');
        }

        return $ticket;
    }

    public function raise(ClientMachine $machine, int $problemTypeId, ?string $description, array $photoPaths = []): Ticket
    {
        return DB::transaction(function () use ($machine, $problemTypeId, $description, $photoPaths) {
            $ticket = $this->repository->create([
                'client_machine_id' => $machine->id,
                'client_account_id' => $machine->client_account_id,
                'problem_type_id' => $problemTypeId,
                'description' => $description,
                'status' => 'open',
            ]);

            foreach ($photoPaths as $path) {
                $ticket->photos()->create(['path' => $path]);
            }

            return $ticket;
        });
    }

    /**
     * Creates a real Job (reusing the existing Jobs/Workers module) for the
     * given worker and links it back to the ticket. The client's machine is
     * off-site, not one of the company's own in-house `machines` fleet, so
     * this always goes through Job's site_name path, never machine_id.
     */
    public function assign(Ticket $ticket, User $actor, int $workerId): Ticket
    {
        if ($ticket->status !== 'open') {
            throw new \InvalidArgumentException('Only an open ticket can be assigned.');
        }

        $machine = $ticket->clientMachine;
        $siteLabel = trim(($machine->clientAccount->name ?? 'Client') . ' - ' . ($machine->product->name ?? 'Machine') . ' (SN: ' . $machine->serial_number . ')');

        $job = $this->jobService->createAssigned([
            'title' => 'Ticket #' . $ticket->id . ': ' . ($ticket->problemType->name ?? 'Service request'),
            'description' => $ticket->description,
            'site_name' => $siteLabel,
            'assigned_to' => $workerId,
        ], $actor);

        $ticket->job_id = $job->id;
        $ticket->status = 'assigned';
        $this->repository->save($ticket);

        $ticket->clientAccount?->notify(new TicketStatusChangedNotification($ticket));

        return $ticket;
    }

    /**
     * Called when the Job created from a ticket moves to in_progress /
     * completed, so the ticket's own status tracks the real work without
     * staff having to update two places.
     */
    public function syncStatusFromJob(Job $job): void
    {
        $ticket = $job->ticket;

        if ($ticket === null) {
            return;
        }

        $newStatus = match ($job->status) {
            'in_progress', 'on_hold' => 'in_progress',
            'completed' => 'resolved',
            default => $ticket->status,
        };

        if ($newStatus === $ticket->status) {
            return;
        }

        $ticket->status = $newStatus;
        $this->repository->save($ticket);

        $ticket->clientAccount?->notify(new TicketStatusChangedNotification($ticket));
    }
}
