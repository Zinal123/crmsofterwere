<?php

namespace App\Services\Ticketing;

use App\Models\ClientMachine;
use App\Repositories\Contracts\ClientAccountRepositoryInterface;
use App\Repositories\Contracts\ClientMachineRepositoryInterface;
use App\Repositories\Contracts\InvoiceRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Support\Collection;

class ClientMachineService
{
    public function __construct(
        private ClientMachineRepositoryInterface $repository,
        private ClientAccountRepositoryInterface $clientAccountRepository,
        private ProductRepositoryInterface $productRepository,
        private InvoiceRepositoryInterface $invoiceRepository,
    ) {
    }

    public function listAll(): Collection
    {
        return $this->repository->allWithDetails();
    }

    public function getCreateViewData(): array
    {
        return [
            'clientAccounts' => $this->clientAccountRepository->allOrderedByLatest(),
            'products' => $this->productRepository->allOrderedByLatest(),
            'invoices' => $this->invoiceRepository->allWithCustomerOrderedByLatest(),
        ];
    }

    public function createMachine(array $data): ClientMachine
    {
        return $this->repository->create([
            'client_account_id' => $data['client_account_id'],
            'product_id' => $data['product_id'],
            'invoice_id' => $data['invoice_id'] ?? null,
            'serial_number' => $data['serial_number'],
            'installed_at' => $data['installed_at'] ?? null,
        ]);
    }
}
