<?php

namespace App\Services\Search;

use App\Models\Employee;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Collection;

class SearchService
{
    private const RESULTS_PER_CATEGORY = 5;

    public function search(User $user, string $query): array
    {
        $results = [];

        if ($user->can('products.view')) {
            $matches = $this->searchProducts($query);
            if ($matches->isNotEmpty()) {
                $results['Products'] = $matches;
            }
        }

        if ($user->can('invoices.view-details')) {
            $matches = $this->searchInvoices($query);
            if ($matches->isNotEmpty()) {
                $results['Invoices'] = $matches;
            }
        }

        if ($user->can('employees.manage')) {
            $matches = $this->searchEmployees($query);
            if ($matches->isNotEmpty()) {
                $results['Employees'] = $matches;
            }
        }

        return $results;
    }

    private function searchProducts(string $query): Collection
    {
        return Product::query()
            ->where('name', 'like', "%{$query}%")
            ->orWhere('make', 'like', "%{$query}%")
            ->orderByDesc('id')
            ->limit(self::RESULTS_PER_CATEGORY)
            ->get(['id', 'name', 'make'])
            ->map(fn (Product $product) => [
                'title' => $product->name,
                'subtitle' => $product->make,
                'url' => route('product'),
            ]);
    }

    private function searchInvoices(string $query): Collection
    {
        return Invoice::query()
            ->join('customer', 'invoice.id', '=', 'customer.invoice_id')
            ->where('invoice.invoice_id', 'like', "%{$query}%")
            ->orWhere('customer.name', 'like', "%{$query}%")
            ->orderByDesc('invoice.id')
            ->limit(self::RESULTS_PER_CATEGORY)
            ->get(['invoice.id', 'invoice.invoice_id', 'customer.name as customer_name'])
            ->map(fn ($invoice) => [
                'title' => $invoice->invoice_id ?: ('Invoice #' . $invoice->id),
                'subtitle' => $invoice->customer_name,
                'url' => route('invoice.details', $invoice->id),
            ]);
    }

    private function searchEmployees(string $query): Collection
    {
        return Employee::query()
            ->where('name', 'like', "%{$query}%")
            ->orWhere('phone', 'like', "%{$query}%")
            ->orderByDesc('id')
            ->limit(self::RESULTS_PER_CATEGORY)
            ->get(['id', 'name', 'phone'])
            ->map(fn (Employee $employee) => [
                'title' => $employee->name,
                'subtitle' => $employee->phone,
                'url' => route('employees.edit', $employee->id),
            ]);
    }
}
