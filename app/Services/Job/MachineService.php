<?php

namespace App\Services\Job;

use App\Models\Machine;
use App\Repositories\Contracts\MachineRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class MachineService
{
    public function __construct(private MachineRepositoryInterface $repository)
    {
    }

    public function list(): Collection
    {
        return $this->repository->all();
    }

    public function create(array $data): Machine
    {
        return $this->repository->create($data);
    }

    public function toggleActive($id): Machine
    {
        return $this->repository->toggleActive($id);
    }

    public function update($id, array $data): Machine
    {
        $machine = $this->repository->find($id);

        if ($machine === null) {
            throw new \InvalidArgumentException('Machine not found.');
        }

        $machine->fill($data);
        $this->repository->save($machine);

        return $machine;
    }
}
