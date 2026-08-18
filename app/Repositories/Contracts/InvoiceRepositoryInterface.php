<?php

namespace App\Repositories\Contracts;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Invoiceproduct;
use App\Models\Paidamount;
use Illuminate\Database\Eloquent\Collection;

interface InvoiceRepositoryInterface
{
    public function createInvoice(array $data): Invoice;

    public function createCustomer(array $data): Customer;

    public function createInvoiceProduct(array $data): Invoiceproduct;

    public function createPaidAmount(array $data): Paidamount;

    public function findInvoice($id): ?Invoice;

    public function saveInvoice(Invoice $invoice): void;

    public function countAllInvoices(): int;

    public function countFilteredInvoices(array $columns, ?string $search, ?array $dateRange = null): int;

    public function getPaginatedInvoiceRows(array $columns, ?string $search, string $orderColumn, string $orderDir, int $start, int $length, ?array $dateRange = null): Collection;

    public function getCustomersByInvoiceId($invoiceId): Collection;

    public function getInvoiceRecords($id): Collection;

    public function getInvoiceProductsWithProductName($invoiceId): Collection;

    public function getPaymentHistory(): Collection;

    public function allWithCustomerOrderedByLatest(): Collection;

    public function findCustomerByInvoiceId($invoiceId): ?Customer;

    public function saveCustomer(Customer $customer): void;

    public function deleteInvoiceCascade($id): void;
}
