@extends('layouts.master')
@section('title') Accounts Dashboard @endsection
@section('content')

<div class="row"><div class="col-12"><div class="page-title-box"><h4 class="mb-0">Accounts Dashboard</h4></div></div></div>

<!-- Row 1: hero + finance overview -->
<div class="row">
    <div class="col-xl-4">
        <div class="card dash-card dash-hero">
            <div class="card-body">
                <h5 class="mb-1">Welcome, {{ Auth::user()->name }}! 💰</h5>
                <p class="text-muted mb-3">Finance overview for this month.</p>
                <h2 class="fw-bold tabular-nums mb-1 {{ $financials['net_profit_month'] >= 0 ? 'text-success' : 'text-danger' }}">₹{{ \App\Support\IndianNumber::format(abs($financials['net_profit_month'])) }}</h2>
                <p class="text-muted mb-3">net {{ $financials['net_profit_month'] >= 0 ? 'profit' : 'loss' }} this month</p>
                <a href="{{ route('accounting.profit-loss') }}" class="btn btn-primary btn-sm">View P&amp;L</a>
            </div>
        </div>
    </div>
    <div class="col-xl-8">
        <div class="card dash-card">
            <div class="card-header border-0">
                <h5 class="card-title mb-0">Finance Overview</h5>
                <p class="text-muted small mb-0">Cash position &amp; balances</p>
            </div>
            <div class="card-body pt-2">
                <div class="row g-4">
                    <div class="col-6 col-md-3">
                        <div class="d-flex align-items-center gap-3">
                            <span class="stat-icon bg-primary-subtle text-primary"><i class="ri-bank-line"></i></span>
                            <div>
                                <p class="text-muted mb-0 small">Cash &amp; Bank</p>
                                <h5 class="mb-0 tabular-nums">₹{{ \App\Support\IndianNumber::format($financials['cash']) }}</h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="d-flex align-items-center gap-3">
                            <span class="stat-icon bg-info-subtle text-info"><i class="ri-arrow-down-circle-line"></i></span>
                            <div>
                                <p class="text-muted mb-0 small">Receivables</p>
                                <h5 class="mb-0 tabular-nums">₹{{ \App\Support\IndianNumber::format($financials['receivable']) }}</h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="d-flex align-items-center gap-3">
                            <span class="stat-icon bg-warning-subtle text-warning"><i class="ri-arrow-up-circle-line"></i></span>
                            <div>
                                <p class="text-muted mb-0 small">Payables</p>
                                <h5 class="mb-0 tabular-nums">₹{{ \App\Support\IndianNumber::format($financials['payable']) }}</h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="d-flex align-items-center gap-3">
                            <span class="stat-icon bg-danger-subtle text-danger"><i class="ri-wallet-3-line"></i></span>
                            <div>
                                <p class="text-muted mb-0 small">Pending Payments</p>
                                <h5 class="mb-0 tabular-nums">₹{{ \App\Support\IndianNumber::format($pendingPayments) }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Row 2: income/expense trend + collection gauge -->
<div class="row">
    <div class="col-xl-8">
        <div class="card dash-card">
            <div class="card-header border-0 d-flex align-items-center">
                <h5 class="card-title mb-0 flex-grow-1">Income vs Expenses</h5>
                <span class="text-muted small">Last 6 months</span>
            </div>
            <div class="card-body pt-0">
                <div id="accountTrendChart" class="oms-skeleton" style="height: 340px;"></div>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <x-ui.metric label="Collection Rate" :value="$collectionRate . '%'" icon="ri-percent-line" color="primary" class="h-100">
            <x-slot:description>Share of invoiced money collected, all-time.</x-slot:description>
        </x-ui.metric>
    </div>
</div>

<!-- Row 3: expenses donut + recent invoices -->
<div class="row">
    <div class="col-xl-5">
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
    <div class="col-xl-7">
        <div class="card dash-card">
            <div class="card-header border-0 d-flex align-items-center">
                <h5 class="card-title mb-0 flex-grow-1">Recent Invoices</h5>
                <a href="{{ route('invoice') }}" class="btn btn-soft-info btn-sm">View all</a>
            </div>
            <div class="card-body pt-0">
                @forelse($recentInvoices->take(6) as $invoice)
                    <div class="txn-item d-flex align-items-center py-2">
                        <span class="stat-icon bg-light text-primary me-3" style="width:40px;height:40px;font-size:1.1rem"><i class="ri-bill-line"></i></span>
                        <div class="flex-grow-1">
                            <a href="{{ route('invoice.details', $invoice->id) }}" class="fw-medium link-primary d-block">#{{ $invoice->id }} · {{ $invoice->customer_name }}</a>
                            <span class="text-muted small">{{ $invoice->date ? date('d M Y', strtotime($invoice->date)) : '' }}</span>
                        </div>
                        <div class="text-end">
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

@endsection

@section('script')
<script src="{{ URL::asset('build/libs/apexcharts/apexcharts.min.js') }}"></script>
<script>
    (function () {
        if (typeof ApexCharts === 'undefined') return;
        var trend = @json($trend);
        var expenseBreakdown = @json(collect($financials['expense_breakdown'])->values());
        var inr = function (v) { return '₹' + Math.round(v).toLocaleString('en-IN'); };

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

        var el = document.querySelector('#accountTrendChart');
        if (el) {
            new ApexCharts(el, {
                chart: { type: 'area', height: 340, toolbar: { show: false }, fontFamily: 'inherit' },
                series: [
                    { name: 'Income', data: trend.map(function (m) { return m.income; }) },
                    { name: 'Expenses', data: trend.map(function (m) { return m.expense; }) }
                ],
                colors: [C.success, C.danger],
                fill: { type: 'gradient', gradient: { opacityFrom: 0.35, opacityTo: 0.05 } },
                stroke: { curve: 'smooth', width: 2 },
                dataLabels: { enabled: false },
                xaxis: { categories: trend.map(function (m) { return m.label; }), labels: { style: { colors: C.text } } },
                yaxis: { labels: { formatter: function (v) { return '₹' + Math.round(v / 1000) + 'k'; }, style: { colors: C.text } } },
                legend: { position: 'top', labels: { colors: C.text } },
                grid: { borderColor: gridColor, strokeDashArray: 4 },
                tooltip: { y: { formatter: inr } }
            }).render().then(function () { dropSkeleton(el); });
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
                tooltip: { y: { formatter: inr } }
            }).render().then(function () { dropSkeleton(donutEl); });
        } else {
            dropSkeleton(donutEl);
        }
    })();
</script>
@endsection
