<?php

namespace App\Repositories\Eloquent;

use App\Models\Bank;
use App\Repositories\Contracts\BankRepositoryInterface;
use App\Support\Tenancy\TenantScope;
use Illuminate\Database\Eloquent\Collection;

class EloquentBankRepository implements BankRepositoryInterface
{
    public function __construct(private TenantScope $tenantScope)
    {
    }

    public function all(): Collection
    {
        return $this->tenantScope->apply(Bank::query())->get();
    }
}
