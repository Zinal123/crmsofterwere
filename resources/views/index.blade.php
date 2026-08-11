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
        <div class="card dash-card dash-hero h-100">
            <div class="card-body">
                <h5 class="mb-1">Welcome back, {{ Auth::user()->name }}! 🎉</h5>
                <p class="text-muted mb-3">Here's your business for {{ $rangeFrom->format('d M') }} &ndash; {{ $rangeTo->format('d M Y') }}.</p>
                <h2 class="text-primary fw-bold tabular-nums mb-1">₹{{ \App\Support\IndianNumber::format($financials['income_month']) }}</h2>
                <p class="mb-3 {{ $financials['net_profit_month'] >= 0 ? 'text-success' : 'text-danger' }}">
                    <i class="ri-{{ $financials['net_profit_month'] >= 0 ? 'arrow-up' : 'arrow-down' }}-line align-bottom"></i>
                    ₹{{ \App\Support\IndianNumber::format(abs($financials['net_profit_month'])) }} net {{ $financials['net_profit_month'] >= 0 ? 'profit' : 'loss' }}
                </p>
                <a href="{{ route('accounting.profit-loss') }}" class="btn btn-primary btn-sm">View Reports</a>
            </div>
        </div>
    </div>
    <div class="col-xl-8">
        <div class="card dash-card h-100">
            <div class="card-body">
                <h5 class="card-title mb-0">Business Overview</h5>
                <p class="text-muted small mb-3">All-time totals across the business</p>
                <div class="row g-4">
                    @php
                        $tiles = [
                            ['Total Revenue', '₹' . \App\Support\IndianNumber::format($totalRevenue), 'ri-money-rupee-circle-line', 'primary'],
                            ['Customers', $totalCustomers, 'ri-group-line', 'success'],
                            ['Products', $productsCount, 'ri-macbook-line', 'warning'],
                            ['Invoices', $totalInvoices, 'ri-file-list-3-line', 'info'],
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
            ['Average Ticket Size', '₹' . \App\Support\IndianNumber::format($avgTicketSize), 'ri-price-tag-3-line', 'secondary', null],
            ['Pending Payments', '₹' . \App\Support\IndianNumber::format($pendingPayments), 'ri-wallet-3-line', 'danger', route('invoice')],
            ['Overdue Jobs', $overdueJobs, 'ri-alarm-warning-line', $overdueJobs > 0 ? 'danger' : 'success', route('jobs.index')],
            ['Low-Stock Items', $lowStockCount, 'ri-stack-line', $lowStockCount > 0 ? 'warning' : 'success', route('invoice.inventrylist')],
        ];
    @endphp
    @foreach($statCards as [$label, $value, $icon, $color, $link])
        <div class="col-xl-3 col-md-6">
            <div class="card dash-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="overflow-hidden">
                            <p class="text-muted mb-1 small text-uppercase text-truncate">{{ $label }}</p>
                            <h4 class="mb-0 tabular-nums text-{{ $color }}">{{ $value }}</h4>
                            @if($link)<a href="{{ $link }}" class="small text-decoration-underline">Details</a>@endif
                        </div>
                        <span class="stat-icon bg-{{ $color }}-subtle text-{{ $color }}"><i class="{{ $icon }}"></i></span>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<!-- ============ CHARTS ============ -->
<h5 class="mb-2 mt-1">Analytics</h5>
<div class="row">
    <div class="col-xl-6">
        <div class="card dash-card h-100">
            <div class="card-header border-0 d-flex align-items-center">
                <h5 class="card-title mb-0 flex-grow-1">Monthly Overview</h5>
                <span class="text-muted small">Income vs Expenses · 6 months</span>
            </div>
            <div class="card-body pt-0">
                <div id="monthlyOverviewChart" style="min-height: 320px;"></div>
            </div>
        </div>
    </div>
    <div class="col-xl-6">
        <div class="card dash-card h-100">
            <div class="card-header border-0 d-flex align-items-center flex-wrap gap-2">
                <h5 class="card-title mb-0 flex-grow-1">Income &amp; Expenses</h5>
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-soft-primary trend-tab active" data-series="income">Income</button>
                    <button type="button" class="btn btn-soft-primary trend-tab" data-series="expense">Expenses</button>
                    <button type="button" class="btn btn-soft-primary trend-tab" data-series="profit">Profit</button>
                </div>
            </div>
            <div class="card-body pt-0">
                <div id="trendTabChart" style="min-height: 320px;"></div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-xl-4">
        <div class="card dash-card h-100">
            <div class="card-header border-0"><h5 class="card-title mb-0">Collection Rate</h5></div>
            <div class="card-body pt-0 text-center">
                <div id="collectionGauge" style="min-height: 260px;"></div>
                <p class="text-muted small mb-0">Share of invoiced money collected</p>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card dash-card h-100">
            <div class="card-header border-0">
                <h5 class="card-title mb-0">Expenses by Category</h5>
                <span class="text-muted small">This month</span>
            </div>
            <div class="card-body pt-0">
                @if(collect($financials['expense_breakdown'])->isNotEmpty())
                    <div id="expenseDonut" style="min-height: 280px;"></div>
                @else
                    <div class="text-center text-muted py-5"><i class="ri-pie-chart-2-line fs-1 d-block mb-2"></i>No expenses recorded this month.</div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card dash-card h-100">
            <div class="card-header border-0 d-flex align-items-center">
                <h5 class="card-title mb-0 flex-grow-1">Revenue Trend</h5>
                <span class="text-muted small tabular-nums">₹{{ \App\Support\IndianNumber::format($totalRevenue) }}</span>
            </div>
            <div class="card-body pt-0">
                <div id="revenueAreaChart" style="min-height: 280px;"></div>
            </div>
        </div>
    </div>
</div>

<!-- ============ TABLES / LISTS ============ -->
<div class="row">
    <div class="col-xl-6">
        <div class="card dash-card h-100">
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
        <div class="card dash-card h-100">
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

        var moEl = document.querySelector('#monthlyOverviewChart');
        if (moEl) {
            new ApexCharts(moEl, {
                chart: { type: 'bar', height: 320, toolbar: { show: false }, fontFamily: 'inherit' },
                series: [
                    { name: 'Income', data: trend.map(function (m) { return m.income; }) },
                    { name: 'Expenses', data: trend.map(function (m) { return m.expense; }) }
                ],
                colors: ['#4361EE', '#F7941D'],
                plotOptions: { bar: { borderRadius: 5, columnWidth: '55%' } },
                dataLabels: { enabled: false },
                xaxis: { categories: labels },
                yaxis: { labels: { formatter: k } },
                legend: { position: 'top' },
                grid: { borderColor: 'rgba(0,0,0,.08)', strokeDashArray: 4 },
                tooltip: { y: { formatter: inr } }
            }).render();
        }

        var gaugeEl = document.querySelector('#collectionGauge');
        if (gaugeEl) {
            new ApexCharts(gaugeEl, {
                chart: { type: 'radialBar', height: 260, fontFamily: 'inherit' },
                series: [{{ (int) $collectionRate }}],
                labels: ['Collected'],
                colors: ['#4361EE'],
                plotOptions: { radialBar: { hollow: { size: '60%' }, dataLabels: {
                    name: { offsetY: 20, color: '#888', fontSize: '13px' },
                    value: { offsetY: -12, fontSize: '26px', fontWeight: 600, formatter: function (v) { return v + '%'; } }
                } } }
            }).render();
        }

        var revEl = document.querySelector('#revenueAreaChart');
        if (revEl) {
            new ApexCharts(revEl, {
                chart: { type: 'area', height: 280, toolbar: { show: false }, fontFamily: 'inherit' },
                series: [{ name: 'Revenue', data: revenueTrend.map(function (m) { return m.revenue; }) }],
                colors: ['#4361EE'],
                fill: { type: 'gradient', gradient: { opacityFrom: 0.35, opacityTo: 0.05 } },
                stroke: { curve: 'smooth', width: 2 },
                dataLabels: { enabled: false },
                xaxis: { categories: revenueTrend.map(function (m) { return m.label; }) },
                yaxis: { labels: { formatter: k } },
                grid: { borderColor: 'rgba(0,0,0,.08)', strokeDashArray: 4 },
                tooltip: { y: { formatter: inr } }
            }).render();
        }

        var donutEl = document.querySelector('#expenseDonut');
        if (donutEl && expenseBreakdown.length) {
            new ApexCharts(donutEl, {
                chart: { type: 'donut', height: 280, fontFamily: 'inherit' },
                series: expenseBreakdown.map(function (c) { return c.total; }),
                labels: expenseBreakdown.map(function (c) { return c.category; }),
                colors: ['#4361EE', '#F7941D', '#10B981', '#7B2FBE', '#EF4444', '#0EA5E9', '#F59E0B'],
                legend: { position: 'bottom' },
                dataLabels: { enabled: true, formatter: function (v) { return Math.round(v) + '%'; } },
                plotOptions: { pie: { donut: { labels: { show: true, total: { show: true, label: 'Total', formatter: function (w) { return inr(w.globals.seriesTotals.reduce(function (a, b) { return a + b; }, 0)); } } } } } },
                tooltip: { y: { formatter: inr } }
            }).render();
        }

        var tabEl = document.querySelector('#trendTabChart');
        var seriesMap = {
            income: { name: 'Income', data: trend.map(function (m) { return m.income; }), color: '#10B981' },
            expense: { name: 'Expenses', data: trend.map(function (m) { return m.expense; }), color: '#F7941D' },
            profit: { name: 'Net Profit', data: trend.map(function (m) { return m.profit; }), color: '#4361EE' }
        };
        if (tabEl) {
            var tabChart = new ApexCharts(tabEl, {
                chart: { type: 'area', height: 320, toolbar: { show: false }, fontFamily: 'inherit' },
                series: [{ name: seriesMap.income.name, data: seriesMap.income.data }],
                colors: [seriesMap.income.color],
                fill: { type: 'gradient', gradient: { opacityFrom: 0.35, opacityTo: 0.05 } },
                stroke: { curve: 'smooth', width: 2 },
                dataLabels: { enabled: false },
                xaxis: { categories: labels },
                yaxis: { labels: { formatter: k } },
                grid: { borderColor: 'rgba(0,0,0,.08)', strokeDashArray: 4 },
                tooltip: { y: { formatter: inr } }
            });
            tabChart.render();
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
