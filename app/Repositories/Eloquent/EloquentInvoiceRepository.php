<?php

namespace App\Repositories\Eloquent;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Invoiceproduct;
use App\Models\Paidamount;
use App\Repositories\Contracts\InvoiceRepositoryInterface;
use App\Support\Tenancy\TenantScope;
use Illuminate\Database\Eloquent\Collection;

class EloquentInvoiceRepository implements InvoiceRepositoryInterface
{
    public function __construct(private TenantScope $tenantScope)
    {
    }

    public function createInvoice(array $data): Invoice
    {
        return Invoice::create($data);
    }

    public function createCustomer(array $data): Customer
    {
        return Customer::create($data);
    }

    public function createInvoiceProduct(array $data): Invoiceproduct
    {
        return Invoiceproduct::create($data);
    }

    public function createPaidAmount(array $data): Paidamount
    {
        return Paidamount::create($data);
    }

    public function findInvoice($id): ?Invoice
    {
        return $this->tenantScope->apply(Invoice::query())->find($id);
    }

    public function saveInvoice(Invoice $invoice): void
    {
        $invoice->save();
    }

    private function invoiceCustomerJoin()
    {
        return $this->tenantScope->apply(
            Invoice::join('customer', 'invoice.id', '=', 'customer.invoice_id', 'left')
        );
    }

    public function countAllInvoices(): int
    {
        return $this->invoiceCustomerJoin()->count();
    }

    public function countFilteredInvoices(array $columns, ?string $search): int
    {
        $query = $this->invoiceCustomerJoin()->select(['invoice.*', 'customer.name', 'customer.phone', 'customer.id as customer_id']);

        if (!empty($search)) {
            $query->where(function ($q) use ($search, $columns) {
                foreach ($columns as $column) {
                    $q->orWhere($column, 'like', '%' . $search . '%');
                }
            });
        }

        return $query->count();
    }

    public function getPaginatedInvoiceRows(array $columns, ?string $search, string $orderColumn, string $orderDir, int $start, int $length): Collection
    {
        $query = $this->invoiceCustomerJoin()->select(['invoice.*', 'customer.name', 'customer.phone', 'customer.id as customer_id']);

        if (!empty($search)) {
            $query->where(function ($q) use ($search, $columns) {
                foreach ($columns as $column) {
                    $q->orWhere($column, 'like', '%' . $search . '%');
                }
            });
        }

        $query->orderBy($orderColumn, $orderDir);

        if ($length > 0) {
            $query->skip($start)->take($length);
        }

        return $query->get();
    }

    public function getCustomersByInvoiceId($invoiceId): Collection
    {
        return $this->tenantScope->apply(Customer::where('invoice_id', $invoiceId))->get();
    }

    public function getInvoiceRecords($id): Collection
    {
        return $this->tenantScope->apply(Invoice::where('id', $id))->get();
    }

    public function getInvoiceProductsWithProductName($invoiceId): Collection
    {
        return $this->tenantScope->apply(
            Invoiceproduct::join('product', 'invoiceproduct.product_name', '=', 'product.id', 'left')
                ->where('invoice_id', $invoiceId)
        )->get(['invoiceproduct.*', 'product.name as product']);
    }

    public function getPaymentHistory(): Collection
    {
        return $this->tenantScope->apply(
            Paidamount::leftJoin('invoice', 'paidamount.invoice_id', '=', 'invoice.id')
                ->leftJoin('customer', 'paidamount.customer_id', '=', 'customer.id')
        )
            ->orderBy('paidamount.id', 'desc')
            ->get(['paidamount.*', 'customer.name as cname']);
    }
}
