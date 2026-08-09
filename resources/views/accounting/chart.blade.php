@extends('layouts.master')
@section('title') Chart of Accounts @endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Accounting @endslot
@slot('title') Chart of Accounts @endslot
@endcomponent

<x-ui.back-link :route="route('reports.index')" label="Back to Reports" />

<div class="d-flex flex-wrap gap-2 mb-3">
    <a href="{{ route('accounting.profit-loss') }}" class="btn btn-soft-primary"><i class="ri-line-chart-line align-middle me-1"></i> Profit &amp; Loss</a>
    <a href="{{ route('accounting.trial-balance') }}" class="btn btn-soft-primary"><i class="ri-scales-3-line align-middle me-1"></i> Trial Balance</a>
</div>

@php
    $typeLabels = [
        'asset' => 'Assets', 'liability' => 'Liabilities', 'equity' => 'Equity',
        'income' => 'Income', 'expense' => 'Expenses',
    ];
@endphp

<x-ui.data-table-card title="Chart of Accounts">
    <p class="text-muted small mb-3">The reference structure the Trial Balance and P&amp;L organise around. Balances are derived from existing invoices, bills, payroll and cash-book entries on a cash basis &mdash; posting entries here is not required.</p>
    <div class="table-responsive">
        <table class="table table-bordered align-middle mb-0">
            <thead>
                <tr>
                    <th style="width:120px">Code</th>
                    <th>Account</th>
                    <th style="width:140px">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($typeLabels as $type => $label)
                    @if(($accounts[$type] ?? collect())->isNotEmpty())
                        <tr class="table-light">
                            <td colspan="3" class="fw-semibold text-uppercase small text-muted">{{ $label }}</td>
                        </tr>
                        @foreach($accounts[$type] as $account)
                            <tr>
                                <td class="font-monospace">{{ $account->code }}</td>
                                <td>{{ $account->name }}</td>
                                <td>
                                    @if($account->is_active)
                                        <x-ui.status-badge status="Active" variant="success" icon="ri-checkbox-circle-line" />
                                    @else
                                        <x-ui.status-badge status="Inactive" variant="secondary" icon="ri-close-circle-line" />
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>
</x-ui.data-table-card>
@endsection
