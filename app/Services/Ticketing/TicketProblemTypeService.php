<?php

namespace App\Services\Ticketing;

use App\Models\TicketProblemType;
use App\Repositories\Contracts\TicketProblemTypeRepositoryInterface;
use Illuminate\Support\Collection;

class TicketProblemTypeService
{
    public function __construct(private TicketProblemTypeRepositoryInterface $repository)
    {
    }

    public function listAll(): Collection
    {
        return $this->repository->all();
    }

    public function listActive(): Collection
    {
        return $this->repository->allActive();
    }

    public function create(array $data): TicketProblemType
    {
        return $this->repository->create([
            'category' => $data['category'],
            'name' => $data['name'],
            'is_active' => true,
        ]);
    }

    public function toggleActive(int $id): TicketProblemType
    {
        $problemType = $this->repository->find($id);

        if ($problemType === null) {
            throw new \InvalidArgumentException('Problem type not found.');
        }

        $problemType->is_active = ! $problemType->is_active;
        $this->repository->save($problemType);

        return $problemType;
    }
}
