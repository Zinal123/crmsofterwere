<?php

namespace App\Repositories\Eloquent;

use App\Models\Ticket;
use App\Repositories\Contracts\TicketRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentTicketRepository implements TicketRepositoryInterface
{
    private const WITH = ['clientMachine.product', 'clientAccount', 'problemType', 'job'];

    public function allWithDetails(): Collection
    {
        return Ticket::with(self::WITH)->orderBy('id', 'desc')->get();
    }

    public function forClientAccount(int $clientAccountId): Collection
    {
        return Ticket::with(self::WITH)->where('client_account_id', $clientAccountId)->orderBy('id', 'desc')->get();
    }

    public function find($id): ?Ticket
    {
        return Ticket::with([...self::WITH, 'photos'])->find($id);
    }

    public function create(array $data): Ticket
    {
        return Ticket::create($data);
    }

    public function save(Ticket $ticket): void
    {
        $ticket->save();
    }
}
