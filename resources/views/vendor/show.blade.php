@extends('layouts.master')
@section('title')
{{ $vendor->name }}
@endsection
@section('content')
<x-ui.back-link :route="route('admin.vendors.index')" label="Back to Vendors" />

<div class="row mb-3">
    <div class="col-md-4">
        <div class="border rounded p-3 text-center">
            <div class="fs-22 fw-semibold">{{ \App\Support\IndianNumber::format($vendor->totalBilled()) }}</div>
            <div class="text-muted small">Total Billed</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="border rounded p-3 text-center">
            <div class="fs-22 fw-semibold text-success">{{ \App\Support\IndianNumber::format($vendor->totalPaid()) }}</div>
            <div class="text-muted small">Total Paid</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="border rounded p-3 text-center">
            <div class="fs-22 fw-semibold {{ $vendor->outstandingBalance() > 0 ? 'text-danger' : 'text-success' }}">{{ \App\Support\IndianNumber::format($vendor->outstandingBalance()) }}</div>
            <div class="text-muted small">Outstanding Balance</div>
        </div>
    </div>
</div>

<div class="row">
    @can('vendor-payments.manage')
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Record a Bill</h5></div>
            <div class="card-body">
                <form action="{{ route('admin.vendors.bills.store', $vendor->id) }}" method="POST">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label" for="bill-number">Bill / Invoice Number</label>
                        <input id="bill-number" type="text" class="form-control" name="bill_number">
                    </div>
                    <div class="mb-2">
                        <label class="form-label" for="bill-amount">Amount <span class="text-danger">*</span></label>
                        <input id="bill-amount" type="number" step="0.01" min="0.01" class="form-control" name="amount" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" for="bill-date">Date <span class="text-danger">*</span></label>
                        <input id="bill-date" type="date" class="form-control" name="date" value="{{ now()->toDateString() }}" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" for="bill-description">Description</label>
                        <textarea id="bill-description" class="form-control" name="description"></textarea>
                    </div>
                    <x-ui.button type="submit" variant="success" icon="ri-add-line">Add Bill</x-ui.button>
                </form>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header"><h5 class="card-title mb-0">Record a Payment</h5></div>
            <div class="card-body">
                <form action="{{ route('admin.vendors.payments.store', $vendor->id) }}" method="POST">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label" for="payment-bill">Against Bill</label>
                        <select id="payment-bill" class="form-select" name="vendor_bill_id">
                            <option value="">General / advance payment (no specific bill)</option>
                            @foreach($bills as $bill)
                                @if($bill->balance() > 0)
                                    <option value="{{ $bill->id }}">{{ $bill->bill_number ?: 'Bill #' . $bill->id }} — balance {{ \App\Support\IndianNumber::format($bill->balance()) }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" for="payment-amount">Amount <span class="text-danger">*</span></label>
                        <input id="payment-amount" type="number" step="0.01" min="0.01" class="form-control" name="amount" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" for="payment-date">Date <span class="text-danger">*</span></label>
                        <input id="payment-date" type="date" class="form-control" name="date" value="{{ now()->toDateString() }}" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" for="payment-mode">Payment Mode <span class="text-danger">*</span></label>
                        <select id="payment-mode" class="form-select" name="payment_mode" required>
                            <option value="cash">Cash</option>
                            <option value="bank">Bank Transfer</option>
                            <option value="upi">UPI</option>
                            <option value="cheque">Cheque</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" for="payment-description">Description</label>
                        <textarea id="payment-description" class="form-control" name="description"></textarea>
                    </div>
                    <x-ui.button type="submit" variant="success" icon="ri-add-line">Record Payment</x-ui.button>
                </form>
            </div>
        </div>
    </div>
    @endcan

    <div class="col-lg-8">
        <x-ui.data-table-card title="Bills">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr><th>Date</th><th>Bill #</th><th>Amount</th><th>Balance</th></tr>
                    </thead>
                    <tbody>
                        @forelse($bills as $bill)
                        <tr>
                            <td>{{ $bill->date->format('d M Y') }}</td>
                            <td>{{ $bill->bill_number ?: '—' }}</td>
                            <td>{{ \App\Support\IndianNumber::format($bill->amount) }}</td>
                            <td>
                                @if($bill->balance() <= 0)
                                    <x-ui.status-badge status="Paid" variant="success" icon="ri-checkbox-circle-line" />
                                @else
                                    <x-ui.status-badge :status="\App\Support\IndianNumber::format($bill->balance()) . ' due'" variant="warning" icon="ri-time-line" />
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4"><x-ui.empty-state icon="ri-file-list-3-line" message="No bills recorded yet." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.data-table-card>

        <x-ui.data-table-card title="Payments">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr><th>Date</th><th>Against</th><th>Amount</th><th>Mode</th></tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                        <tr>
                            <td>{{ $payment->date->format('d M Y') }}</td>
                            <td>{{ $payment->bill ? ($payment->bill->bill_number ?: 'Bill #' . $payment->bill->id) : 'General / advance' }}</td>
                            <td>{{ \App\Support\IndianNumber::format($payment->amount) }}</td>
                            <td>{{ ucfirst($payment->payment_mode) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4"><x-ui.empty-state icon="ri-money-dollar-circle-line" message="No payments recorded yet." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.data-table-card>
    </div>
</div>
@endsection
