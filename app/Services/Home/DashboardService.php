<?php

namespace App\Services\Home;

use App\Models\Invetry;
use App\Models\Job;
use App\Models\Ticket;
use App\Models\User;
use App\Repositories\Contracts\DashboardRepositoryInterface;
use App\Repositories\Contracts\JobRepositoryInterface;
use App\Services\Reporting\AccountingService;
use App\Support\Tenancy\TenantScope;
use Illuminate\Support\Carbon;

class DashboardService
{
    public function __construct(
        private DashboardRepositoryInterface $repository,
        private JobRepositoryInterface $jobRepository,
        private AccountingService $accounting,
        private TenantScope $tenantScope,
    ) {
    }

    /**
     * Owner: the strategic view. Business KPIs, a live financial snapshot
     * (from the accounting reporting layer), operational alerts, and the
     * existing product/invoice tables.
     */
    public function ownerViewData(): array
    {
        return array_merge([
            'totalRevenue' => $this->repository->getTotalRevenue(),
            'totalInvoices' => $this->repository->getTotalInvoices(),
            'totalCustomers' => $this->repository->getTotalCustomers(),
            'pendingPayments' => $this->repository->getPendingPayments(),
            'topProducts' => $this->repository->getTopProducts(),
            'recentInvoices' => $this->repository->getRecentInvoices(),
            'jobStats' => $this->jobRepository->ownerDashboardStats(),
            'financials' => $this->financialSnapshot(),
            'trend' => $this->accounting->monthlyTrend(6),
        ], $this->operationalAlerts());
    }

    /**
     * Manager: the operational view. Job pipeline, alerts, and how the team
     * is performing today - no revenue/cash figures.
     */
    public function managerViewData(): array
    {
        return array_merge([
            'jobStats' => $this->jobRepository->ownerDashboardStats(),
            'openTickets' => $this->tenantScope->apply(Ticket::whereIn('status', ['open', 'assigned', 'in_progress']))->count(),
        ], $this->operationalAlerts());
    }

    /**
     * Account: the finance view. Cash position, receivables/payables,
     * this-month P&L and the recent-invoice ledger - no job operations.
     */
    public function accountViewData(): array
    {
        return [
            'totalRevenue' => $this->repository->getTotalRevenue(),
            'pendingPayments' => $this->repository->getPendingPayments(),
            'recentInvoices' => $this->repository->getRecentInvoices(),
            'financials' => $this->financialSnapshot(),
            'trend' => $this->accounting->monthlyTrend(6),
        ];
    }

    /**
     * Worker: the "my day" view. Only their own jobs - counts and the active
     * queue - matching the jobs.view-own scope they already have.
     */
    public function workerViewData(User $user): array
    {
        $jobs = $this->jobRepository->allForUser($user);

        return [
            'assignedToday' => $jobs->where('status', 'assigned')->count(),
            'inProgress' => $jobs->where('status', 'in_progress')->count(),
            'onHold' => $jobs->where('status', 'on_hold')->count(),
            'completedToday' => $jobs->where('status', 'completed')
                ->filter(fn (Job $job) => $job->completed_at && $job->completed_at->isToday())->count(),
            'overdue' => $jobs->whereNotNull('overdue_flagged_at')
                ->whereNotIn('status', ['completed', 'rejected'])->count(),
            'activeJobs' => $jobs->whereIn('status', ['assigned', 'in_progress', 'on_hold'])
                ->sortBy('due_date')->take(8),
        ];
    }

    /** Live financial snapshot shared by the Owner and Account dashboards. */
    private function financialSnapshot(): array
    {
        $pl = $this->accounting->profitAndLoss(Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth());
        $tb = $this->accounting->trialBalance(Carbon::now());
        $byCode = $tb['rows']->keyBy('code');

        return [
            'net_profit_month' => $pl['net_profit'],
            'income_month' => $pl['total_income'],
            'expense_month' => $pl['total_expense'],
            'top_expense' => $pl['expense_breakdown']->first(),
            'expense_breakdown' => $pl['expense_breakdown'],
            'cash' => (float) ($byCode['1000']['debit'] ?? 0) - (float) ($byCode['1000']['credit'] ?? 0),
            'receivable' => (float) ($byCode['1100']['debit'] ?? 0),
            'payable' => (float) ($byCode['2000']['credit'] ?? 0),
        ];
    }

    /** Operational alert counts shared by the Owner and Manager dashboards. */
    private function operationalAlerts(): array
    {
        return [
            'overdueJobs' => $this->tenantScope->apply(
                Job::whereNotNull('overdue_flagged_at')->whereNotIn('status', ['completed', 'rejected'])
            )->count(),
            'lowStockCount' => Invetry::all()->filter(
                fn (Invetry $item) => $item->quantity < $item->effectiveLowStockThreshold()
            )->count(),
        ];
    }
}
