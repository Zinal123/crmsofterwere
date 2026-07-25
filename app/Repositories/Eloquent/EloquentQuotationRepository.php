<?php

namespace App\Repositories\Eloquent;

use App\Models\Quation;
use App\Models\QuotationItem;
use App\Repositories\Contracts\QuotationRepositoryInterface;
use App\Support\Tenancy\TenantScope;
use Illuminate\Database\Eloquent\Collection;

class EloquentQuotationRepository implements QuotationRepositoryInterface
{
    public function __construct(private TenantScope $tenantScope)
    {
    }

    public function allOrderedByLatest(): Collection
    {
        return $this->tenantScope->apply(Quation::orderBy('id', 'desc'))->get();
    }

    public function create(array $data): Quation
    {
        return Quation::create($data);
    }

    public function createItem(int $quotationId, array $item): QuotationItem
    {
        return QuotationItem::create([
            'quotation_id' => $quotationId,
            'description' => $item['description'] ?? null,
            'amount' => $item['amount'] ?? null,
        ]);
    }
}
