<?php

namespace App\Repositories\Eloquent;

use App\Models\Customer;
use App\Models\Invoice;
use App\Repositories\Contracts\DashboardRepositoryInterface;
use App\Support\Tenancy\TenantScope;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EloquentDashboardRepository implements DashboardRepositoryInterface
{
    public function __construct(private TenantScope $tenantScope)
    {
    }

    public function getTotalRevenue(): float
    {
        return (float) $this->tenantScope->apply(Invoice::query())->sum('amountwithtax');
    }

    public function getTotalInvoices(): int
    {
        return $this->tenantScope->apply(Invoice::query())->count();
    }

    public function getTotalCustomers(): int
    {
        return $this->tenantScope->apply(Customer::query())->count();
    }

    public function getPendingPayments(): float
    {
        return (float) $this->tenantScope->apply(Invoice::query())->sum('remaining_amount');
    }

    public function getTopProducts(): Collection
    {
        // Raw query builder (not Eloquent), so it can't go through
        // TenantScope::apply() which is typed to Eloquent\Builder - same
        // inconsistent-coverage gap already noted in the Product
        // Configuration pilot for write operations.
        return DB::table('invoiceproduct')
            ->join('product', 'invoiceproduct.product_name', '=', 'product.id')
            ->select('product.id', 'product.name', DB::raw('SUM(invoiceproduct.quantity) as total_qty'), DB::raw('SUM(invoiceproduct.totalamount) as total_revenue'))
            ->groupBy('product.id', 'product.name')
            ->orderByDesc('total_revenue')
            ->limit(5)
            ->get();
    }

    public function getRecentInvoices(): Collection
    {
        return $this->tenantScope->apply(
            Invoice::join('customer', 'invoice.id', '=', 'customer.invoice_id')
        )
            ->orderByDesc('invoice.id')
            ->limit(10)
            ->get(['invoice.*', 'customer.name as customer_name']);
    }
}
