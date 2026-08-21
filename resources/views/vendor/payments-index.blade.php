@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <x-ui.back-link :route="route('admin.vendors.index')" label="Back to Vendors" />
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Vendor Payments</h5>
            <form method="GET" action="{{ route('vendor-payments.index') }}" class="row g-2 align-items-end mb-3">
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
                        <a href="{{ route('vendor-payments.index') }}" class="btn btn-outline-secondary">Clear</a>
                    </div>
                @endif
            </form>
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle tabular-nums">
                    <thead><tr><th>Date</th><th>Vendor</th><th>Amount (₹)</th><th>Mode</th><th>Description</th></tr></thead>
                    <tbody>
                        @forelse($payments as $payment)
                            <tr>
                                <td>{{ $payment->date->toDateString() }}</td>
                                <td><a href="{{ route('admin.vendors.show', $payment->vendor_id) }}">{{ $payment->vendor->name ?? 'Unknown' }}</a></td>
                                <td>{{ \App\Support\IndianNumber::format($payment->amount) }}</td>
                                <td>{{ ucfirst($payment->payment_mode) }}</td>
                                <td>{{ $payment->description ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5"><x-ui.empty-state icon="ri-money-dollar-circle-line" message="No vendor payments in this range." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
