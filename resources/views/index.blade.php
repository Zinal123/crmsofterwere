@extends('layouts.master')
@section('title') @lang('translation.dashboards') @endsection
@section('content')

<div class="row">
    <div class="col">

        <div class="h-100">
            <div class="row mb-3 pb-1">
                <div class="col-12">
                    <div class="d-flex align-items-lg-center flex-lg-row flex-column">
                        <div class="flex-grow-1">
                            <h4 class="fs-16 mb-1">Welcome, {{ Auth::user()->name }}!</h4>
                            <p class="text-muted mb-0">Here's what's happening with your business
                                today.</p>
                        </div>
                        <div class="mt-3 mt-lg-0">
                            <form action="javascript:void(0);">
                                <div class="row g-3 mb-0 align-items-center">
                                    <div class="col-sm-auto">
                                        <div class="input-group">
                                            <input type="text" class="form-control border-0 dash-filter-picker shadow" data-provider="flatpickr" data-range-date="true" data-date-format="d M, Y" data-deafult-date="01 Jan 2022 to 31 Jan 2022">
                                            <div class="input-group-text bg-primary border-primary text-white">
                                                <i class="ri-calendar-2-line"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <!--end col-->
                                    <div class="col-auto">
                                      <!-- Grids in modals -->

                                    </div>
                                    <!--end col-->
                                </div>
                                <!--end row-->
                            </form>
                        </div>
                    </div><!-- end card header -->
                </div>
                <!--end col-->
            </div>
            <!--end row-->

            <div class="row">
                <div class="col-xl-3 col-md-6">
                    <!-- card -->
                    <div class="card card-animate">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1 overflow-hidden">
                                    <p class="text-uppercase fw-medium text-muted text-truncate mb-0">
                                        Total Revenue</p>
                                </div>
                            </div>
                            <div class="d-flex align-items-end justify-content-between mt-4">
                                <div>
                                    <h4 class="fs-22 fw-semibold ff-secondary mb-4">₹{{ \App\Support\IndianNumber::format($totalRevenue) }}</h4>
                                    <a href="{{ route('invoice') }}" class="text-decoration-underline">View all invoices</a>
                                </div>
                                <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title rounded fs-3 text-white" style="background:#4361EE;">
                                        <i class="ri-money-rupee-circle-line"></i>
                                    </span>
                                </div>
                            </div>
                        </div><!-- end card body -->
                    </div><!-- end card -->
                </div><!-- end col -->

                <div class="col-xl-3 col-md-6">
                    <!-- card -->
                    <div class="card card-animate">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1 overflow-hidden">
                                    <p class="text-uppercase fw-medium text-muted text-truncate mb-0">
                                        Total Invoices</p>
                                </div>
                            </div>
                            <div class="d-flex align-items-end justify-content-between mt-4">
                                <div>
                                    <h4 class="fs-22 fw-semibold ff-secondary mb-4">{{ $totalInvoices }}</h4>
                                    <a href="{{ route('invoice') }}" class="text-decoration-underline">View all invoices</a>
                                </div>
                                <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title rounded fs-3 text-white" style="background:#F7941D;">
                                        <i class="ri-shopping-bag-3-line"></i>
                                    </span>
                                </div>
                            </div>
                        </div><!-- end card body -->
                    </div><!-- end card -->
                </div><!-- end col -->

                <div class="col-xl-3 col-md-6">
                    <!-- card -->
                    <div class="card card-animate">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1 overflow-hidden">
                                    <p class="text-uppercase fw-medium text-muted text-truncate mb-0">
                                        Customers</p>
                                </div>
                            </div>
                            <div class="d-flex align-items-end justify-content-between mt-4">
                                <div>
                                    <h4 class="fs-22 fw-semibold ff-secondary mb-4">{{ $totalCustomers }}</h4>
                                    <a href="{{ route('invoice.vender') }}" class="text-decoration-underline">See details</a>
                                </div>
                                <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title rounded fs-3 text-white" style="background:#10B981;">
                                        <i class="ri-user-line"></i>
                                    </span>
                                </div>
                            </div>
                        </div><!-- end card body -->
                    </div><!-- end card -->
                </div><!-- end col -->

                <div class="col-xl-3 col-md-6">
                    <!-- card -->
                    <div class="card card-animate">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1 overflow-hidden">
                                    <p class="text-uppercase fw-medium text-muted text-truncate mb-0">
                                        Pending Payments</p>
                                </div>
                            </div>
                            <div class="d-flex align-items-end justify-content-between mt-4">
                                <div>
                                    <h4 class="fs-22 fw-semibold ff-secondary mb-4">₹{{ \App\Support\IndianNumber::format($pendingPayments) }}</h4>
                                    <a href="{{ route('invoice') }}" class="text-decoration-underline">View all invoices</a>
                                </div>
                                <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title rounded fs-3 text-white" style="background:#7B2FBE;">
                                        <i class="ri-wallet-3-line"></i>
                                    </span>
                                </div>
                            </div>
                        </div><!-- end card body -->
                    </div><!-- end card -->
                </div><!-- end col -->
            </div> <!-- end row-->

            <!-- Financial snapshot (this month), from the accounting reporting layer -->
            <div class="row">
                <div class="col-xl-3 col-md-6">
                    <div class="card card-animate">
                        <div class="card-body">
                            <p class="text-uppercase fw-medium text-muted mb-2">Net {{ $financials['net_profit_month'] >= 0 ? 'Profit' : 'Loss' }} (This Month)</p>
                            <h4 class="mb-1 {{ $financials['net_profit_month'] >= 0 ? 'text-success' : 'text-danger' }}">₹{{ \App\Support\IndianNumber::format(abs($financials['net_profit_month'])) }}</h4>
                            <a href="{{ route('accounting.profit-loss') }}" class="text-decoration-underline small">View P&amp;L</a>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card card-animate">
                        <div class="card-body">
                            <p class="text-uppercase fw-medium text-muted mb-2">Cash &amp; Bank</p>
                            <h4 class="mb-1">₹{{ \App\Support\IndianNumber::format($financials['cash']) }}</h4>
                            <a href="{{ route('accounting.trial-balance') }}" class="text-decoration-underline small">Trial balance</a>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card card-animate">
                        <div class="card-body">
                            <p class="text-uppercase fw-medium text-muted mb-2">Receivables</p>
                            <h4 class="mb-1 text-info">₹{{ \App\Support\IndianNumber::format($financials['receivable']) }}</h4>
                            <a href="{{ route('invoice') }}" class="text-decoration-underline small">Unpaid invoices</a>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card card-animate">
                        <div class="card-body">
                            <p class="text-uppercase fw-medium text-muted mb-2">Payables</p>
                            <h4 class="mb-1 text-warning">₹{{ \App\Support\IndianNumber::format($financials['payable']) }}</h4>
                            <a href="{{ route('expenses.cashbook') }}" class="text-decoration-underline small">Vendor dues</a>
                        </div>
                    </div>
                </div>
            </div> <!-- end financial row -->

            <!-- Operational alerts -->
            @if($overdueJobs > 0 || $lowStockCount > 0)
            <div class="row">
                @if($overdueJobs > 0)
                <div class="col-xl-6">
                    <a href="{{ route('jobs.index') }}" class="text-reset">
                        <div class="card border border-danger border-opacity-25">
                            <div class="card-body d-flex align-items-center">
                                <div class="avatar-sm flex-shrink-0 me-3">
                                    <span class="avatar-title rounded bg-danger-subtle text-danger fs-3"><i class="ri-alarm-warning-line"></i></span>
                                </div>
                                <div>
                                    <h4 class="mb-0 text-danger">{{ $overdueJobs }}</h4>
                                    <p class="text-muted mb-0">Overdue job(s) need attention</p>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                @endif
                @if($lowStockCount > 0)
                <div class="col-xl-6">
                    <a href="{{ route('invoice.inventrylist') }}" class="text-reset">
                        <div class="card border border-warning border-opacity-25">
                            <div class="card-body d-flex align-items-center">
                                <div class="avatar-sm flex-shrink-0 me-3">
                                    <span class="avatar-title rounded bg-warning-subtle text-warning fs-3"><i class="ri-stack-line"></i></span>
                                </div>
                                <div>
                                    <h4 class="mb-0 text-warning">{{ $lowStockCount }}</h4>
                                    <p class="text-muted mb-0">Inventory item(s) low on stock</p>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                @endif
            </div>
            @endif

            <!-- Charts: turn the numbers into a picture -->
            <div class="row">
                <div class="col-xl-8">
                    <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <h4 class="card-title mb-0 flex-grow-1">Income vs Expenses</h4>
                            <span class="text-muted small">Last 6 months</span>
                        </div>
                        <div class="card-body">
                            <div id="incomeExpenseChart" style="min-height: 300px;"></div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Expenses by Category</h4>
                            <span class="text-muted small">This month</span>
                        </div>
                        <div class="card-body">
                            @if(collect($financials['expense_breakdown'])->isNotEmpty())
                                <div id="expenseDonut" style="min-height: 300px;"></div>
                            @else
                                <div class="text-center text-muted py-5"><i class="ri-pie-chart-2-line fs-1 d-block mb-2"></i>No expenses recorded this month.</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            @can('jobs.view-all')
            <div class="row">
                <div class="col-xl-3 col-md-6">
                    <div class="card card-animate">
                        <div class="card-body">
                            <p class="text-uppercase fw-medium text-muted mb-0">Jobs Completed Today</p>
                            <h4 class="mb-0">{{ $jobStats['completed_today'] }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card card-animate">
                        <div class="card-body">
                            <p class="text-uppercase fw-medium text-muted mb-0">Pending Approval</p>
                            <h4 class="mb-0">{{ $jobStats['pending_approval'] }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card card-animate">
                        <div class="card-body">
                            <p class="text-uppercase fw-medium text-muted mb-0">Pending Completion</p>
                            <h4 class="mb-0">{{ $jobStats['pending_completion'] }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card card-animate">
                        <div class="card-body">
                            <p class="text-uppercase fw-medium text-muted mb-0">Jobs by Worker (Today)</p>
                            @forelse($jobStats['by_worker'] as $workerName => $count)
                                <p class="mb-0 small">{{ $workerName }}: {{ $count }}</p>
                            @empty
                                <p class="mb-0 small text-muted">No completions yet today.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
            @endcan

            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">Top Products by Revenue</h4>
                        </div><!-- end card header -->

                        <div class="card-body">
                            <div class="table-responsive table-card">
                                <table class="table table-hover table-centered align-middle table-nowrap mb-0">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Rate</th>
                                            <th>Quantity Sold</th>
                                            <th>Revenue</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($topProducts as $item)
                                        <tr>
                                            <td>
                                                <h5 class="fs-14 my-1">{{ $item->name }}</h5>
                                            </td>
                                            <td>
                                                <h5 class="fs-14 my-1 fw-normal">₹{{ \App\Support\IndianNumber::format($item->total_revenue / max($item->total_qty, 1)) }}</h5>
                                            </td>
                                            <td>
                                                <h5 class="fs-14 my-1 fw-normal">{{ $item->total_qty }}</h5>
                                            </td>
                                            <td>
                                                <h5 class="fs-14 my-1 fw-normal">₹{{ \App\Support\IndianNumber::format($item->total_revenue) }}</h5>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">No invoices yet.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">Recent Invoices</h4>
                            <div class="flex-shrink-0">
                                <a href="{{ route('invoice') }}" class="btn btn-soft-info btn-sm">
                                    <i class="ri-file-list-3-line align-middle"></i> View All Invoices
                                </a>
                            </div>
                        </div><!-- end card header -->

                        <div class="card-body">
                            <div class="table-responsive table-card">
                                <table class="table table-borderless table-centered align-middle table-nowrap mb-0">
                                    <thead class="text-muted table-light">
                                        <tr>
                                            <th scope="col">Invoice ID</th>
                                            <th scope="col">Customer</th>
                                            <th scope="col">Date</th>
                                            <th scope="col">Amount</th>
                                            <th scope="col">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($recentInvoices as $invoice)
                                        <tr>
                                            <td>
                                                <a href="{{ route('invoice.details', $invoice->id) }}" class="fw-medium link-primary">#{{ $invoice->id }}</a>
                                            </td>
                                            <td>{{ $invoice->customer_name }}</td>
                                            <td>{{ $invoice->date ? date('d-M-y', strtotime($invoice->date)) : '' }}</td>
                                            <td>
                                                <span class="text-success">₹{{ \App\Support\IndianNumber::format($invoice->amountwithtax) }}</span>
                                            </td>
                                            <td>
                                                @if($invoice->amount == $invoice->paidamount)
                                                <span class="badge bg-success-subtle text-success">Paid</span>
                                                @else
                                                <span class="badge bg-warning-subtle text-warning">Pending</span>
                                                @endif
                                            </td>
                                        </tr><!-- end tr -->
                                        @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">No invoices yet.</td>
                                        </tr>
                                        @endforelse
                                    </tbody><!-- end tbody -->
                                </table><!-- end table -->
                            </div>
                        </div>
                    </div> <!-- .card-->
                </div> <!-- .col-->
            </div> <!-- end row-->

        </div> <!-- end .h-100-->

    </div> <!-- end col -->

</div>

@endsection

@section('script')
<script src="{{ URL::asset('build/libs/apexcharts/apexcharts.min.js') }}"></script>
<script>
    (function () {
        var trend = @json($trend);
        var expenseBreakdown = @json(collect($financials['expense_breakdown'])->values());

        // Income vs Expenses - grouped columns over the last 6 months.
        if (document.querySelector('#incomeExpenseChart') && typeof ApexCharts !== 'undefined') {
            new ApexCharts(document.querySelector('#incomeExpenseChart'), {
                chart: { type: 'bar', height: 320, toolbar: { show: false }, fontFamily: 'inherit' },
                series: [
                    { name: 'Income', data: trend.map(function (m) { return m.income; }) },
                    { name: 'Expenses', data: trend.map(function (m) { return m.expense; }) }
                ],
                colors: ['#10B981', '#F7941D'],
                plotOptions: { bar: { borderRadius: 4, columnWidth: '55%' } },
                dataLabels: { enabled: false },
                stroke: { show: true, width: 2, colors: ['transparent'] },
                xaxis: { categories: trend.map(function (m) { return m.label; }) },
                yaxis: { labels: { formatter: function (v) { return '₹' + Math.round(v).toLocaleString('en-IN'); } } },
                legend: { position: 'top' },
                grid: { borderColor: 'rgba(0,0,0,.08)', strokeDashArray: 4 },
                tooltip: { y: { formatter: function (v) { return '₹' + Math.round(v).toLocaleString('en-IN'); } } }
            }).render();
        }

        // Expenses by category - donut.
        if (document.querySelector('#expenseDonut') && expenseBreakdown.length && typeof ApexCharts !== 'undefined') {
            new ApexCharts(document.querySelector('#expenseDonut'), {
                chart: { type: 'donut', height: 320, fontFamily: 'inherit' },
                series: expenseBreakdown.map(function (c) { return c.total; }),
                labels: expenseBreakdown.map(function (c) { return c.category; }),
                colors: ['#4361EE', '#F7941D', '#10B981', '#7B2FBE', '#EF4444', '#0EA5E9', '#F59E0B'],
                legend: { position: 'bottom' },
                dataLabels: { enabled: true, formatter: function (val) { return Math.round(val) + '%'; } },
                plotOptions: { pie: { donut: { labels: { show: true, total: { show: true, label: 'Total', formatter: function (w) { return '₹' + Math.round(w.globals.seriesTotals.reduce(function (a, b) { return a + b; }, 0)).toLocaleString('en-IN'); } } } } } },
                tooltip: { y: { formatter: function (v) { return '₹' + Math.round(v).toLocaleString('en-IN'); } } }
            }).render();
        }
    })();
</script>
@endsection
