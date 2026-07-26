<?php

namespace App\Repositories\Contracts;

use App\Models\Ticket;
use Illuminate\Support\Collection;

interface TicketRepositoryInterface
{
    public function allWithDetails(): Collection;

    public function forClientAccount(int $clientAccountId): Collection;

    public function find($id): ?Ticket;

    public function create(array $data): Ticket;

    public function save(Ticket $ticket): void;
}
