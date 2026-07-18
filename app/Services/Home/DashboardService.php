<?php

namespace App\Services\Home;

use App\Repositories\Contracts\DashboardRepositoryInterface;
use App\Repositories\Contracts\JobRepositoryInterface;

class DashboardService
{
    public function __construct(
        private DashboardRepositoryInterface $repository,
        private JobRepositoryInterface $jobRepository,
    ) {
    }

    public function getDashboardViewData(): array
    {
        return [
            'totalRevenue' => $this->repository->getTotalRevenue(),
            'totalInvoices' => $this->repository->getTotalInvoices(),
            'totalCustomers' => $this->repository->getTotalCustomers(),
            'pendingPayments' => $this->repository->getPendingPayments(),
            'topProducts' => $this->repository->getTopProducts(),
            'recentInvoices' => $this->repository->getRecentInvoices(),
            'jobStats' => $this->jobRepository->ownerDashboardStats(),
        ];
    }
}
