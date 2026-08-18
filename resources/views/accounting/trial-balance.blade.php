@extends('layouts.master')
@section('title') Trial Balance @endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Accounting @endslot
@slot('title') Trial Balance @endslot
@endcomponent

<x-ui.back-link :route="route('accounting.chart')" label="Back to Chart of Accounts" />

<x-ui.data-table-card title="Trial Balance">
    <form method="GET" action="{{ route('accounting.trial-balance') }}" class="row g-2 align-items-end mb-3">
        <div class="col-auto">
            <label for="as_of" class="form-label small mb-1">As of</label>
            <input type="date" id="as_of" name="as_of" class="form-control" value="{{ $report['as_of']->toDateString() }}">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary"><i class="ri-filter-3-line align-middle me-1"></i> Apply</button>
        </div>
    </form>

    <p class="text-muted small mb-3">Derived from cash-book, invoice, vendor and payroll records as of {{ $report['as_of']->format('d M Y') }}. Owner&rsquo;s Equity is the balancing figure &mdash; these books are kept on a cash basis, not a posted double-entry journal.</p>

    <div class="table-responsive">
        <table class="table table-bordered align-middle mb-0 tabular-nums">
            <thead>
                <tr>
                    <th style="width:100px">Code</th>
                    <th>Account</th>
                    <th class="text-end" style="width:180px">Debit</th>
                    <th class="text-end" style="width:180px">Credit</th>
                </tr>
            </thead>
            <tbody>
                @foreach($report['rows'] as $row)
                    <tr>
                        <td class="font-monospace">{{ $row['code'] }}</td>
                        <td>
                            @if(isset($row['link']))
                                <a href="{{ $row['link'] }}">{{ $row['name'] }}</a>
                            @else
                                {{ $row['name'] }}
                                <i class="ri-information-line text-muted" title="No drill-down for this row - it's either a computed figure (Cash, GST Payable, Owner's Equity) or would need an unpaid-only filter that doesn't exist yet (Receivable, Payable)."></i>
                            @endif
                        </td>
                        <td class="text-end">{{ $row['debit'] != 0 ? \App\Support\IndianNumber::format($row['debit']) : '—' }}</td>
                        <td class="text-end">{{ $row['credit'] != 0 ? \App\Support\IndianNumber::format($row['credit']) : '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="border-top border-2">
                    <td colspan="2" class="fw-bold">Total</td>
                    <td class="text-end fw-bold">{{ \App\Support\IndianNumber::format($report['total_debit']) }}</td>
                    <td class="text-end fw-bold">{{ \App\Support\IndianNumber::format($report['total_credit']) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</x-ui.data-table-card>
@endsection
