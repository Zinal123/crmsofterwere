<?php

namespace App\Repositories\Contracts;

use App\Models\Bank;
use Illuminate\Database\Eloquent\Collection;

interface BankRepositoryInterface
{
    public function all(): Collection;

    public function find(int $id): ?Bank;
}
