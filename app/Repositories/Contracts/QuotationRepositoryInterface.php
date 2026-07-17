<?php

namespace App\Repositories\Contracts;

use App\Models\Quation;
use Illuminate\Database\Eloquent\Collection;

interface QuotationRepositoryInterface
{
    public function allOrderedByLatest(): Collection;

    public function create(array $data): Quation;
}
