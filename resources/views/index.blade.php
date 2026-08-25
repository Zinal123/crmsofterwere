@extends('layouts.master')
@section('title') @lang('translation.dashboards') @endsection

@section('content')

<!-- Page title + date range filter -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Dashboard</h4>
            <form action="{{ route('root') }}" method="GET" class="d-flex align-items-end gap-2">
                <div>
                    <label for="from" class="form-label small text-muted mb-1">From</label>
                    <input type="date" id="from" name="from" class="form-control form-control-sm" value="{{ $rangeFrom->toDateString() }}">
                </div>
                <div>
                    <label for="to" class="form-label small text-muted mb-1">To</label>
                    <input type="date" id="to" name="to" class="form-control form-control-sm" value="{{ $rangeTo->toDateString() }}">
                </div>
                <button type="submit" class="btn btn-primary btn-sm"><i class="ri-filter-3-line align-middle"></i> Apply</button>
            </form>
        </div>
    </div>
</div>

<!-- Hero + business overview -->
<div class="row">
    <div class="col-xl-4">
        <div class="card dash-card dash-hero">
            <div class="card-body">
                <h5 class="mb-1">Welcome back, {{ Auth::user()->name }}! 🎉</h5>
                <p class="text-muted mb-3">Here's your business for {{ $rangeFrom->format('d M') }} &ndash; {{ $rangeTo->format('d M Y') }}.</p>
                <h2 class="text-primary fw-bold tabular-nums mb-1">₹{{ \App\Support\IndianNumber::format($financials['income_month']) }}</h2>
                <p class="mb-1 {{ $financials['net_profit_month'] >= 0 ? 'text-success' : 'text-danger' }}">
                    <i class="ri-{{ $financials['net_profit_month'] >= 0 ? 'arrow-up' : 'arrow-down' }}-line align-bottom"></i>
                    ₹{{ \App\Support\IndianNumber::format(abs($financials['net_profit_month'])) }} net {{ $financials['net_profit_month'] >= 0 ? 'profit' : 'loss' }}
                </p>
                @if($netProfitChangePct !== null)
                    <p class="mb-3">
                        <span class="badge bg-{{ $netProfitChangePct >= 0 ? 'success' : 'danger' }}-subtle text-{{ $netProfitChangePct >= 0 ? 'success' : 'danger' }}">
                            <i class="ri-arrow-{{ $netProfitChangePct >= 0 ? 'up' : 'down' }}-line align-bottom"></i> {{ number_format(abs($netProfitChangePct), 1) }}% vs last month
                        </span>
                    </p>
                @else
                    <div class="mb-3"></div>
                @endif
                <a href="{{ route('accounting.profit-loss') }}" class="btn btn-primary btn-sm">View Reports</a>
            </div>
        </div>
    </div>
    <div class="col-xl-8">
        <div class="card dash-card">
            <div class="card-body">
                <h5 class="card-title mb-0">Business Overview</h5>
                <p class="text-muted small mb-3">All-time totals across the business</p>
                <div class="row g-4">
                    @php
                        $tiles = [
                            ['Total Revenue', '₹' . \App\Support\IndianNumber::format($totalRevenue), 'ri-money-rupee-circle-line', 'primary'],
                            ['Average Ticket Size', '₹' . \App\Support\IndianNumber::format($avgTicketSize), 'ri-price-tag-3-line', 'info'],
                            ['Customers', $totalCustomers, 'ri-group-line', 'success'],
                            ['Invoices', $totalInvoices, 'ri-file-list-3-line', 'warning'],
                        ];
                    @endphp
                    @foreach($tiles as [$label, $value, $icon, $color])
                        <div class="col-6 col-md-3">
                            <div class="d-flex align-items-center gap-2">
                                <span class="stat-icon bg-{{ $color }}-subtle text-{{ $color }}"><i class="{{ $icon }}"></i></span>
                                <div class="overflow-hidden">
                                    <p class="text-muted mb-0 small text-truncate">{{ $label }}</p>
                                    <h6 class="mb-0 tabular-nums">{{ $value }}</h6>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============ CARDS: financial + operational stat cards ============ -->
<div class="d-flex align-items-center mb-2 mt-1">
    <h5 class="mb-0 flex-grow-1">Financials</h5>
    <span class="badge bg-primary-subtle text-primary"><i class="ri-calendar-2-line align-middle me-1"></i>{{ $rangeFrom->format('d M Y') }} &ndash; {{ $rangeTo->format('d M Y') }}</span>
</div>
<div class="row">
    @php
        $statCards = [
            ['Net ' . ($financials['net_profit_month'] >= 0 ? 'Profit' : 'Loss'), '₹' . \App\Support\IndianNumber::format(abs($financials['net_profit_month'])), 'ri-funds-line', $financials['net_profit_month'] >= 0 ? 'success' : 'danger', null],
            ['Cash & Bank', '₹' . \App\Support\IndianNumber::format($financials['cash']), 'ri-bank-line', 'primary', route('accounting.trial-balance')],
            ['Receivables', '₹' . \App\Support\IndianNumber::format($financials['receivable']), 'ri-arrow-down-circle-line', 'info', route('invoice')],
            ['Payables', '₹' . \App\Support\IndianNumber::format($financials['payable']), 'ri-arrow-up-circle-line', 'warning', route('expenses.cashbook')],
        ];
    @endphp
    @foreach($statCards as [$label, $value, $icon, $color, $link])
        <div class="col-xl-3 col-md-6">
            <x-ui.metric :label="$label" :value="$value" :icon="$icon" :color="$color" :link="$link" />
        </div>
    @endforeach
</div>

<!-- Conditional alerts (only shown when something needs attention) -->
@if($overdueJobs > 0 || $lowStockCount > 0)
<div class="row">
    @if($overdueJobs > 0)
    <div class="col-md-6">
        <a href="{{ route('jobs.index') }}" class="text-reset">
            <div class="card dash-card border border-danger border-opacity-25 mb-3">
                <div class="card-body d-flex align-items-center py-2">
                    <span class="stat-icon bg-danger-subtle text-danger me-3"><i class="ri-alarm-warning-line"></i></span>
                    <div><h5 class="mb-0 text-danger tabular-nums">{{ $overdueJobs }}</h5><span class="text-muted small">Overdue job(s) need attention</span></div>
                </div>
            </div>
        </a>
    </div>
    @endif
    @if($lowStockCount > 0)
    <div class="col-md-6">
        <a href="{{ route('invoice.inventrylist') }}" class="text-reset">
            <div class="card dash-card border border-warning border-opacity-25 mb-3">
                <div class="card-body d-flex align-items-center py-2">
                    <span class="stat-icon bg-warning-subtle text-warning me-3"><i class="ri-stack-line"></i></span>
                    <div><h5 class="mb-0 text-warning tabular-nums">{{ $lowStockCount }}</h5><span class="text-muted small">Inventory item(s) low on stock</span></div>
                </div>
            </div>
        </a>
    </div>
    @endif
</div>
@endif

<!-- ============ CHARTS ============ -->
<h5 class="mb-2 mt-1">Analytics</h5>
<div class="row">
    <div class="col-xl-6">
        <div class="card dash-card">
            <div class="card-header border-0 d-flex align-items-center">
                <h5 class="card-title mb-0 flex-grow-1">Monthly Overview</h5>
                <span class="text-muted small">Income vs Expenses · 6 months</span>
            </div>
            <div class="card-body pt-0">
                <div id="monthlyOverviewChart" class="oms-skeleton" style="height: 340px;"></div>
            </div>
        </div>
    </div>
    <div class="col-xl-6">
        <div class="card dash-card">
            <div class="card-header border-0 d-flex align-items-center flex-wrap gap-2">
                <h5 class="card-title mb-0 flex-grow-1">Income &amp; Expenses</h5>
                {{-- role="group" on .btn-group is Bootstrap's own documented pattern for a button toolbar, not a form fieldset. --}}
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-soft-primary trend-tab active" data-series="income">Income</button>
                    <button type="button" class="btn btn-soft-primary trend-tab" data-series="expense">Expenses</button>
                    <button type="button" class="btn btn-soft-primary trend-tab" data-series="profit">Profit</button>
                </div>
            </div>
            <div class="card-body pt-0">
                <div id="trendTabChart" class="oms-skeleton" style="height: 340px;"></div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-xl-4">
        <x-ui.metric label="Collection Rate" :value="$collectionRate . '%'" icon="ri-percent-line" color="primary" class="h-100">
            <x-slot:description>Share of invoiced money collected, all-time.</x-slot:description>
        </x-ui.metric>
    </div>
    <div class="col-xl-4">
        <div class="card dash-card">
            <div class="card-header border-0">
                <h5 class="card-title mb-0">Expenses by Category</h5>
                <span class="text-muted small">This month</span>
            </div>
            <div class="card-body pt-0">
                @if(collect($financials['expense_breakdown'])->isNotEmpty())
                    <div id="expenseDonut" class="oms-skeleton" style="height: 320px;"></div>
                @else
                    <div class="text-center text-muted py-5"><i class="ri-pie-chart-2-line fs-1 d-block mb-2"></i>No expenses recorded this month.</div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card dash-card">
            <div class="card-header border-0 d-flex align-items-center">
                <h5 class="card-title mb-0 flex-grow-1">Revenue Trend</h5>
                <span class="text-muted small tabular-nums">₹{{ \App\Support\IndianNumber::format($totalRevenue) }}</span>
            </div>
            <div class="card-body pt-0">
                <div id="revenueAreaChart" class="oms-skeleton" style="height: 320px;"></div>
            </div>
        </div>
    </div>
</div>

<!-- ============ TABLES / LISTS ============ -->
<div class="row">
    <div class="col-xl-6">
        <div class="card dash-card">
            <div class="card-header border-0">
                <h5 class="card-title mb-0">Top Products by Revenue</h5>
            </div>
            <div class="card-body pt-0">
                @php $maxRev = max((float) optional($topProducts->first())->total_revenue, 1); @endphp
                @forelse($topProducts as $item)
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="fw-medium text-truncate" style="max-width: 60%">{{ $item->name }}</span>
                            <span class="tabular-nums">₹{{ \App\Support\IndianNumber::format($item->total_revenue) }}</span>
                        </div>
                        <div class="progress top-progress bg-light">
                            {{-- Bootstrap's div+role="progressbar" pattern, not a native <progress> - keeps the bg-color theming/dark-mode support native <progress> can't do. --}}
                        <div class="progress-bar bg-primary" role="progressbar" style="width: {{ round($item->total_revenue / $maxRev * 100) }}%"></div>
                        </div>
                        <span class="text-muted small tabular-nums">{{ $item->total_qty }} sold</span>
                    </div>
                @empty
                    <p class="text-muted text-center py-4 mb-0">No invoices yet.</p>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-xl-6">
        <div class="card dash-card">
            <div class="card-header border-0 d-flex align-items-center">
                <h5 class="card-title mb-0 flex-grow-1">Recent Invoices</h5>
                <a href="{{ route('invoice') }}" class="btn btn-soft-info btn-sm">View all</a>
            </div>
            <div class="card-body pt-0">
                @forelse($recentInvoices->take(6) as $invoice)
                    <div class="txn-item d-flex align-items-center py-2">
                        <span class="stat-icon bg-light text-primary me-3" style="width:40px;height:40px;font-size:1.1rem"><i class="ri-bill-line"></i></span>
                        <div class="flex-grow-1 overflow-hidden">
                            <a href="{{ route('invoice.details', $invoice->id) }}" class="fw-medium link-primary d-block text-truncate">#{{ $invoice->id }} · {{ $invoice->customer_name }}</a>
                            <span class="text-muted small">{{ $invoice->date ? date('d M Y', strtotime($invoice->date)) : '' }}</span>
                        </div>
                        <div class="text-end ms-2">
                            <span class="fw-semibold tabular-nums d-block">₹{{ \App\Support\IndianNumber::format($invoice->amountwithtax) }}</span>
                            @if($invoice->amount == $invoice->paidamount)
                                <span class="badge bg-success-subtle text-success">Paid</span>
                            @else
                                <span class="badge bg-warning-subtle text-warning">Pending</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-muted text-center py-4 mb-0">No invoices yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card dash-card">
            <div class="card-header border-0 d-flex align-items-center">
                <h5 class="card-title mb-0 flex-grow-1">Technician Performance</h5>
                <span class="text-muted small">Completed jobs &amp; avg. turnaround</span>
            </div>
            <div class="card-body pt-0">
                <div class="table-responsive">
                    <table class="table table-hover table-centered align-middle mb-0 tabular-nums">
                        <thead class="text-muted table-light">
                            <tr>
                                <th>Technician</th>
                                <th class="text-end">Jobs Completed</th>
                                <th class="text-end">Avg. Hours</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($technicianPerformance as $tech)
                            <tr>
                                <td class="fw-medium">{{ $tech['name'] }}</td>
                                <td class="text-end">{{ $tech['completed_count'] }}</td>
                                <td class="text-end">{{ $tech['avg_completion_hours'] !== null ? $tech['avg_completion_hours'] . ' h' : '—' }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center text-muted">No technicians with completed jobs yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <p class="text-muted small mb-0 mt-2"><i class="ri-information-line align-middle"></i> Revenue isn't linked to individual technicians in this system, so this shows job throughput rather than revenue per technician.</p>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
<script src="{{ URL::asset('build/libs/apexcharts/apexcharts.min.js') }}"></script>
<script>
    (function () {
        if (typeof ApexCharts === 'undefined') return;

        var trend = @json($trend);
        var revenueTrend = @json($revenueTrend);
        var expenseBreakdown = @json(collect($financials['expense_breakdown'])->values());
        var labels = trend.map(function (m) { return m.label; });
        var inr = function (v) { return '₹' + Math.round(v).toLocaleString('en-IN'); };
        var k = function (v) { return '₹' + Math.round(v / 1000) + 'k'; };

        // Design-system tokens (kept in sync with build/css/brand-theme.css).
        // Brand orange is reserved for identity contexts only - charts use
        // primary blue plus semantic success/danger/info so a series color
        // always means the same thing it means on a badge.
        var isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
        var C = isDark
            ? { primary: '#6C93E8', success: '#5FD79A', danger: '#F09088', info: '#7DB2F0', warning: '#F0BA52', text: '#C7CEDB' }
            : { primary: '#2954A6', success: '#16794F', danger: '#B42318', info: '#1B5FA6', warning: '#9A6400', text: '#3A4358' };
        var gridColor = isDark ? 'rgba(255,255,255,.08)' : 'rgba(0,0,0,.08)';
        var donutPalette = [C.primary, C.success, C.danger, C.info, C.warning, '#7B5CC4', '#5B84E0'];

        function dropSkeleton(target) {
            if (target) target.classList.remove('oms-skeleton');
        }

        var moEl = document.querySelector('#monthlyOverviewChart');
        if (moEl) {
            new ApexCharts(moEl, {
                chart: { type: 'bar', height: 340, toolbar: { show: false }, fontFamily: 'inherit' },
                series: [
                    { name: 'Income', data: trend.map(function (m) { return m.income; }) },
                    { name: 'Expenses', data: trend.map(function (m) { return m.expense; }) }
                ],
                colors: [C.success, C.danger],
                plotOptions: { bar: { borderRadius: 5, columnWidth: '55%' } },
                dataLabels: { enabled: false },
                xaxis: { categories: labels, labels: { style: { colors: C.text } } },
                yaxis: { labels: { formatter: k, style: { colors: C.text } } },
                legend: { position: 'top', labels: { colors: C.text } },
                grid: { borderColor: gridColor, strokeDashArray: 4 },
                tooltip: { y: { formatter: inr } }
            }).render().then(function () { dropSkeleton(moEl); });
        }

        var revEl = document.querySelector('#revenueAreaChart');
        if (revEl) {
            new ApexCharts(revEl, {
                chart: { type: 'area', height: 320, toolbar: { show: false }, fontFamily: 'inherit' },
                series: [{ name: 'Revenue', data: revenueTrend.map(function (m) { return m.revenue; }) }],
                colors: [C.primary],
                fill: { type: 'gradient', gradient: { opacityFrom: 0.35, opacityTo: 0.05 } },
                stroke: { curve: 'smooth', width: 2 },
                dataLabels: { enabled: false },
                xaxis: { categories: revenueTrend.map(function (m) { return m.label; }), labels: { style: { colors: C.text } } },
                yaxis: { labels: { formatter: k, style: { colors: C.text } } },
                grid: { borderColor: gridColor, strokeDashArray: 4 },
                tooltip: { y: { formatter: inr } }
            }).render().then(function () { dropSkeleton(revEl); });
        }

        var donutEl = document.querySelector('#expenseDonut');
        if (donutEl && expenseBreakdown.length) {
            new ApexCharts(donutEl, {
                chart: { type: 'donut', height: 320, fontFamily: 'inherit' },
                series: expenseBreakdown.map(function (c) { return c.total; }),
                labels: expenseBreakdown.map(function (c) { return c.category; }),
                colors: donutPalette,
                legend: { position: 'bottom', labels: { colors: C.text } },
                dataLabels: { enabled: true, formatter: function (v) { return Math.round(v) + '%'; } },
                plotOptions: { pie: { donut: { labels: { show: true, total: { show: true, label: 'Total', formatter: function (w) { return inr(w.globals.seriesTotals.reduce(function (a, b) { return a + b; }, 0)); } } } } } },
                tooltip: { y: { formatter: inr } }
            }).render().then(function () { dropSkeleton(donutEl); });
        } else {
            dropSkeleton(donutEl);
        }

        var tabEl = document.querySelector('#trendTabChart');
        var seriesMap = {
            income: { name: 'Income', data: trend.map(function (m) { return m.income; }), color: C.success },
            expense: { name: 'Expenses', data: trend.map(function (m) { return m.expense; }), color: C.danger },
            profit: { name: 'Net Profit', data: trend.map(function (m) { return m.profit; }), color: C.primary }
        };
        if (tabEl) {
            var tabChart = new ApexCharts(tabEl, {
                chart: { type: 'area', height: 340, toolbar: { show: false }, fontFamily: 'inherit' },
                series: [{ name: seriesMap.income.name, data: seriesMap.income.data }],
                colors: [seriesMap.income.color],
                fill: { type: 'gradient', gradient: { opacityFrom: 0.35, opacityTo: 0.05 } },
                stroke: { curve: 'smooth', width: 2 },
                dataLabels: { enabled: false },
                xaxis: { categories: labels, labels: { style: { colors: C.text } } },
                yaxis: { labels: { formatter: k, style: { colors: C.text } } },
                grid: { borderColor: gridColor, strokeDashArray: 4 },
                tooltip: { y: { formatter: inr } }
            });
            tabChart.render().then(function () { dropSkeleton(tabEl); });
            document.querySelectorAll('.trend-tab').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    document.querySelectorAll('.trend-tab').forEach(function (b) { b.classList.remove('active'); });
                    btn.classList.add('active');
                    var s = seriesMap[btn.getAttribute('data-series')];
                    tabChart.updateOptions({ colors: [s.color] });
                    tabChart.updateSeries([{ name: s.name, data: s.data }]);
                });
            });
        }
    })();
</script>
@endsection
