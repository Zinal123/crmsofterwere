<?php

namespace App\Repositories\Contracts;

use App\Models\Quation;
use App\Models\QuotationItem;
use Illuminate\Database\Eloquent\Collection;

interface QuotationRepositoryInterface
{
    public function allOrderedByLatest(): Collection;

    public function create(array $data): Quation;

    public function createItem(int $quotationId, array $item): QuotationItem;

    public function delete(int $id): void;

    public function findWithDetails(int $id): Quation;

    public function find(int $id): ?Quation;

    public function save(Quation $quotation): void;
}
