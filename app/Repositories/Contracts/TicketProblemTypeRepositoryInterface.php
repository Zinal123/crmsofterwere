<?php

namespace App\Repositories\Contracts;

use App\Models\TicketProblemType;
use Illuminate\Support\Collection;

interface TicketProblemTypeRepositoryInterface
{
    public function all(): Collection;

    public function allActive(): Collection;

    public function find($id): ?TicketProblemType;

    public function create(array $data): TicketProblemType;

    public function save(TicketProblemType $problemType): void;
}
