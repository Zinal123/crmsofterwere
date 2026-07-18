<?php

namespace App\Repositories\Eloquent;

use App\Models\Machine;
use App\Repositories\Contracts\MachineRepositoryInterface;
use App\Support\Tenancy\TenantScope;
use Illuminate\Database\Eloquent\Collection;

class EloquentMachineRepository implements MachineRepositoryInterface
{
    public function __construct(private TenantScope $tenantScope)
    {
    }

    public function all(): Collection
    {
        return $this->tenantScope->apply(Machine::orderBy('name'))->get();
    }

    public function create(array $data): Machine
    {
        return Machine::create($data);
    }

    public function find($id): ?Machine
    {
        return $this->tenantScope->apply(Machine::query())->find($id);
    }

    public function toggleActive($id): Machine
    {
        $machine = Machine::findOrFail($id);
        $machine->is_active = ! $machine->is_active;
        $machine->save();

        return $machine;
    }
}
