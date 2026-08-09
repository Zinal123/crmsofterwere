<?php

namespace App\Repositories\Contracts;

use App\Models\Machine;
use Illuminate\Database\Eloquent\Collection;

interface MachineRepositoryInterface
{
    public function all(): Collection;

    public function create(array $data): Machine;

    public function find($id): ?Machine;

    public function toggleActive($id): Machine;

    public function save(Machine $machine): void;
}
