@extends('layouts.master')
@section('title') Accounts Dashboard @endsection
@section('content')
<div class="row mb-3">
    <div class="col-12">
        <h4 class="fs-16 mb-1">Welcome, {{ Auth::user()->name }}!</h4>
        <p class="text-muted mb-0">Finance overview for this month.</p>
    </div>
</div>

<div class="row">
    <div class="col-6 col-xl-3">
        <div class="card card-animate">
            <div class="card-body">
                <p class="text-uppercase fw-medium text-muted mb-2">Net {{ $financials['net_profit_month'] >= 0 ? 'Profit' : 'Loss' }} (Month)</p>
                <h4 class="mb-1 {{ $financials['net_profit_month'] >= 0 ? 'text-success' : 'text-danger' }}">₹{{ \App\Support\IndianNumber::format(abs($financials['net_profit_month'])) }}</h4>
                <a href="{{ route('accounting.profit-loss') }}" class="text-decoration-underline small">P&amp;L</a>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card card-animate">
            <div class="card-body">
                <p class="text-uppercase fw-medium text-muted mb-2">Cash &amp; Bank</p>
                <h4 class="mb-1">₹{{ \App\Support\IndianNumber::format($financials['cash']) }}</h4>
                <a href="{{ route('accounting.trial-balance') }}" class="text-decoration-underline small">Trial balance</a>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card card-animate">
            <div class="card-body">
                <p class="text-uppercase fw-medium text-muted mb-2">Receivables</p>
                <h4 class="mb-1 text-info">₹{{ \App\Support\IndianNumber::format($financials['receivable']) }}</h4>
                <a href="{{ route('invoice') }}" class="text-decoration-underline small">Invoices</a>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card card-animate">
            <div class="card-body">
                <p class="text-uppercase fw-medium text-muted mb-2">Payables</p>
                <h4 class="mb-1 text-warning">₹{{ \App\Support\IndianNumber::format($financials['payable']) }}</h4>
                <a href="{{ route('expenses.cashbook') }}" class="text-decoration-underline small">Vendor dues</a>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex align-items-center">
        <h4 class="card-title mb-0 flex-grow-1">Income vs Expenses</h4>
        <span class="text-muted small">Last 6 months</span>
    </div>
    <div class="card-body">
        <div id="accountTrendChart" style="min-height: 300px;"></div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex align-items-center">
        <h4 class="card-title mb-0 flex-grow-1">Recent Invoices</h4>
        <a href="{{ route('invoice') }}" class="btn btn-soft-info btn-sm"><i class="ri-file-list-3-line align-middle me-1"></i> View all</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-borderless align-middle mb-0">
                <thead class="text-muted table-light">
                    <tr><th>Invoice ID</th><th>Customer</th><th>Date</th><th>Amount</th><th>Status</th></tr>
                </thead>
                <tbody>
                    @forelse($recentInvoices as $invoice)
                        <tr>
                            <td><a href="{{ route('invoice.details', $invoice->id) }}" class="fw-medium link-primary">#{{ $invoice->id }}</a></td>
                            <td>{{ $invoice->customer_name }}</td>
                            <td>{{ $invoice->date ? date('d-M-y', strtotime($invoice->date)) : '' }}</td>
                            <td><span class="text-success">₹{{ \App\Support\IndianNumber::format($invoice->amountwithtax) }}</span></td>
                            <td>
                                @if($invoice->amount == $invoice->paidamount)
                                    <span class="badge bg-success-subtle text-success">Paid</span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning">Pending</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">No invoices yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="{{ URL::asset('build/libs/apexcharts/apexcharts.min.js') }}"></script>
<script>
    (function () {
        var trend = @json($trend);
        var el = document.querySelector('#accountTrendChart');
        if (el && typeof ApexCharts !== 'undefined') {
            new ApexCharts(el, {
                chart: { type: 'area', height: 320, toolbar: { show: false }, fontFamily: 'inherit' },
                series: [
                    { name: 'Income', data: trend.map(function (m) { return m.income; }) },
                    { name: 'Expenses', data: trend.map(function (m) { return m.expense; }) }
                ],
                colors: ['#10B981', '#F7941D'],
                fill: { type: 'gradient', gradient: { opacityFrom: 0.35, opacityTo: 0.05 } },
                stroke: { curve: 'smooth', width: 2 },
                dataLabels: { enabled: false },
                xaxis: { categories: trend.map(function (m) { return m.label; }) },
                yaxis: { labels: { formatter: function (v) { return '₹' + Math.round(v).toLocaleString('en-IN'); } } },
                legend: { position: 'top' },
                grid: { borderColor: 'rgba(0,0,0,.08)', strokeDashArray: 4 },
                tooltip: { y: { formatter: function (v) { return '₹' + Math.round(v).toLocaleString('en-IN'); } } }
            }).render();
        }
    })();
</script>
@endsection
