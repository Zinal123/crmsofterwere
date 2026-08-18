@extends('layouts.master')
@section('title') Profit &amp; Loss @endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Accounting @endslot
@slot('title') Profit &amp; Loss @endslot
@endcomponent

<x-ui.back-link :route="route('accounting.chart')" label="Back to Chart of Accounts" />

<x-ui.data-table-card title="Profit &amp; Loss Statement">
    <form method="GET" action="{{ route('accounting.profit-loss') }}" class="row g-2 align-items-end mb-3">
        <div class="col-auto">
            <label for="from" class="form-label small mb-1">From</label>
            <input type="date" id="from" name="from" class="form-control" value="{{ $report['from']->toDateString() }}">
        </div>
        <div class="col-auto">
            <label for="to" class="form-label small mb-1">To</label>
            <input type="date" id="to" name="to" class="form-control" value="{{ $report['to']->toDateString() }}">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary"><i class="ri-filter-3-line align-middle me-1"></i> Apply</button>
        </div>
    </form>

    <p class="text-muted small mb-3">Cash basis, {{ $report['from']->format('d M Y') }} &ndash; {{ $report['to']->format('d M Y') }}. Wages and vendor payments are counted once, from payroll and vendor records &mdash; not double-counted from mirrored cash-book entries.</p>

    <div class="table-responsive">
        <table class="table table-bordered align-middle mb-0 tabular-nums">
            <tbody>
                <tr class="table-light">
                    <td class="fw-semibold text-uppercase small text-muted" colspan="2">Income</td>
                </tr>
                @foreach($report['income'] as $line)
                    <tr>
                        <td class="ps-4">
                            @if(isset($line['link']))
                                <a href="{{ $line['link'] }}">{{ $line['account'] }}</a>
                            @else
                                {{ $line['account'] }}
                            @endif
                        </td>
                        <td class="text-end">{{ \App\Support\IndianNumber::format($line['amount']) }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td class="fw-semibold">Total Income</td>
                    <td class="text-end fw-semibold text-success">{{ \App\Support\IndianNumber::format($report['total_income']) }}</td>
                </tr>

                <tr class="table-light">
                    <td class="fw-semibold text-uppercase small text-muted" colspan="2">Expenses</td>
                </tr>
                @foreach($report['expenses'] as $line)
                    <tr>
                        <td class="ps-4">
                            @if(isset($line['link']))
                                <a href="{{ $line['link'] }}">{{ $line['account'] }}</a>
                            @else
                                {{ $line['account'] }}
                            @endif
                        </td>
                        <td class="text-end">{{ \App\Support\IndianNumber::format($line['amount']) }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td class="fw-semibold">Total Expenses</td>
                    <td class="text-end fw-semibold text-danger">{{ \App\Support\IndianNumber::format($report['total_expense']) }}</td>
                </tr>

                <tr class="border-top border-2">
                    <td class="fw-bold fs-15">Net {{ $report['net_profit'] >= 0 ? 'Profit' : 'Loss' }}</td>
                    <td class="text-end fw-bold fs-15 {{ $report['net_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ \App\Support\IndianNumber::format(abs($report['net_profit'])) }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    @if($report['expense_breakdown']->isNotEmpty())
        <h6 class="mt-4 mb-2">Operating Expenses by Category</h6>
        <div class="table-responsive">
            <table class="table table-sm table-bordered align-middle mb-0 tabular-nums">
                <thead>
                    <tr><th>Category</th><th class="text-end" style="width:200px">Amount</th></tr>
                </thead>
                <tbody>
                    @foreach($report['expense_breakdown'] as $row)
                        <tr>
                            <td>{{ $row['category'] }}</td>
                            <td class="text-end">{{ \App\Support\IndianNumber::format($row['total']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-ui.data-table-card>
@endsection
