<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface BankRepositoryInterface
{
    public function all(): Collection;
}
