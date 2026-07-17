<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;

interface DashboardRepositoryInterface
{
    public function getTotalRevenue(): float;

    public function getTotalInvoices(): int;

    public function getTotalCustomers(): int;

    public function getPendingPayments(): float;

    public function getTopProducts(): Collection;

    public function getRecentInvoices(): Collection;
}
