<?php

namespace App\Services\Home;

use App\Models\Invetry;
use App\Models\Job;
use App\Models\Product;
use App\Models\Ticket;
use App\Models\User;
use App\Repositories\Contracts\DashboardRepositoryInterface;
use App\Repositories\Contracts\JobRepositoryInterface;
use App\Services\Reporting\AccountingService;
use App\Services\Reporting\ReportService;
use App\Support\Tenancy\TenantScope;
use Illuminate\Support\Carbon;

class DashboardService
{
    public function __construct(
        private DashboardRepositoryInterface $repository,
        private JobRepositoryInterface $jobRepository,
        private AccountingService $accounting,
        private ReportService $reports,
        private TenantScope $tenantScope,
    ) {
    }

    /**
     * Owner: the strategic view. Business KPIs, a live financial snapshot
     * (from the accounting reporting layer), operational alerts, and the
     * existing product/invoice tables.
     */
    public function ownerViewData(?Carbon $from = null, ?Carbon $to = null): array
    {
        $from = $from ?: Carbon::now()->startOfMonth();
        $to = $to ?: Carbon::now()->endOfMonth();

        return array_merge([
            'totalRevenue' => $this->repository->getTotalRevenue(),
            'totalInvoices' => $this->repository->getTotalInvoices(),
            'totalCustomers' => $this->repository->getTotalCustomers(),
            'pendingPayments' => $this->repository->getPendingPayments(),
            'topProducts' => $this->repository->getTopProducts(),
            'recentInvoices' => $this->repository->getRecentInvoices(),
            'jobStats' => $this->jobRepository->ownerDashboardStats(),
            'technicianPerformance' => $this->technicianPerformance(),
            'avgTicketSize' => $this->averageTicketSize(),
            'productsCount' => Product::count(),
            'collectionRate' => $this->collectionRate(),
            'financials' => $this->financialSnapshot($from, $to),
            'trend' => $this->accounting->monthlyTrend(6),
            'revenueTrend' => $this->accounting->monthlyRevenue(6),
            'rangeFrom' => $from,
            'rangeTo' => $to,
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
            'collectionRate' => $this->collectionRate(),
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

    /**
     * Live financial snapshot shared by the Owner and Account dashboards.
     * Income/expense/profit cover the given range; cash and party balances
     * are point-in-time as of the range end.
     */
    private function financialSnapshot(?Carbon $from = null, ?Carbon $to = null): array
    {
        $from = $from ?: Carbon::now()->startOfMonth();
        $to = $to ?: Carbon::now()->endOfMonth();

        $pl = $this->accounting->profitAndLoss($from, $to);
        $tb = $this->accounting->trialBalance($to);
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

    /**
     * Per-technician completed-job count and average completion time. Invoices
     * aren't linked to jobs in this app, so true revenue-per-technician can't
     * be derived - this is the honest productivity view instead. Top 5 by
     * completed jobs.
     */
    private function technicianPerformance()
    {
        return $this->reports->technicianPerformance()
            ->sortByDesc('completed_count')
            ->take(5)
            ->values();
    }

    /** Average invoice value = total revenue / number of invoices. */
    private function averageTicketSize(): float
    {
        $count = $this->repository->getTotalInvoices();

        return $count > 0 ? round($this->repository->getTotalRevenue() / $count, 2) : 0.0;
    }

    /** Share of invoiced money actually collected, as a whole-number percent. */
    private function collectionRate(): int
    {
        $revenue = $this->repository->getTotalRevenue();

        return $revenue > 0 ? (int) round(($revenue - $this->repository->getPendingPayments()) / $revenue * 100) : 0;
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
