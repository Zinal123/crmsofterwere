@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <x-ui.back-link :route="route('employees.index')" label="Back to Employees" />
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Salary Payments</h5>
            <form method="GET" action="{{ route('payroll.index') }}" class="row g-2 align-items-end mb-3">
                <div class="col-auto">
                    <label for="from" class="form-label small mb-1">From</label>
                    <input type="date" id="from" name="from" class="form-control" value="{{ $from }}">
                </div>
                <div class="col-auto">
                    <label for="to" class="form-label small mb-1">To</label>
                    <input type="date" id="to" name="to" class="form-control" value="{{ $to }}">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary"><i class="ri-filter-3-line align-middle me-1"></i> Apply</button>
                </div>
                @if($from || $to)
                    <div class="col-auto">
                        <a href="{{ route('payroll.index') }}" class="btn btn-outline-secondary">Clear</a>
                    </div>
                @endif
            </form>
            <div class="table-responsive">
                <table class="table table-bordered align-middle tabular-nums">
                    <thead><tr><th>Date</th><th>Employee</th><th>Amount (₹)</th><th>Note</th></tr></thead>
                    <tbody>
                        @forelse($payments as $payment)
                            <tr>
                                <td>{{ $payment->date->toDateString() }}</td>
                                <td>
                                    <a href="{{ route('employees.payroll', ['employee' => $payment->employee_id, 'year' => $payment->date->year, 'month' => $payment->date->month]) }}">
                                        {{ $payment->employee->name ?? 'Unknown' }}
                                    </a>
                                </td>
                                <td>{{ \App\Support\IndianNumber::format($payment->amount) }}</td>
                                <td>{{ $payment->note ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4"><x-ui.empty-state icon="ri-money-rupee-circle-line" message="No salary payments in this range." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
