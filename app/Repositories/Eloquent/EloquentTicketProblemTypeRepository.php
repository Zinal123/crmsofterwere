<?php

namespace App\Repositories\Eloquent;

use App\Models\TicketProblemType;
use App\Repositories\Contracts\TicketProblemTypeRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentTicketProblemTypeRepository implements TicketProblemTypeRepositoryInterface
{
    public function all(): Collection
    {
        return TicketProblemType::orderBy('category')->orderBy('name')->get();
    }

    public function allActive(): Collection
    {
        return TicketProblemType::where('is_active', true)->orderBy('category')->orderBy('name')->get();
    }

    public function find($id): ?TicketProblemType
    {
        return TicketProblemType::find($id);
    }

    public function create(array $data): TicketProblemType
    {
        return TicketProblemType::create($data);
    }

    public function save(TicketProblemType $problemType): void
    {
        $problemType->save();
    }
}
