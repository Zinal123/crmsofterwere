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
                        <tr><th>Date</th><th>Bill #</th><th>Amount</th><th>Balance</th>@can('vendor-payments.manage')<th></th>@endcan</tr>
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
                            @can('vendor-payments.manage')
                            <td>
                                <div class="d-flex gap-1 flex-wrap">
                                    <button type="button" class="btn btn-soft-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editBill-{{ $bill->id }}" title="Edit" aria-label="Edit">
                                        <i class="ri-edit-line align-bottom"></i>
                                    </button>
                                    <form action="{{ route('admin.vendors.bills.destroy', [$vendor->id, $bill->id]) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-soft-danger btn-sm" data-confirm-delete title="Delete" aria-label="Delete">
                                            <i class="ri-delete-bin-fill align-bottom"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                            @endcan
                        </tr>
                        @empty
                        <tr><td colspan="5"><x-ui.empty-state icon="ri-file-list-3-line" message="No bills recorded yet." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.data-table-card>

        <x-ui.data-table-card title="Payments">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr><th>Date</th><th>Against</th><th>Amount</th><th>Mode</th>@can('vendor-payments.manage')<th></th>@endcan</tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                        <tr>
                            <td>{{ $payment->date->format('d M Y') }}</td>
                            <td>{{ $payment->bill ? ($payment->bill->bill_number ?: 'Bill #' . $payment->bill->id) : 'General / advance' }}</td>
                            <td>{{ \App\Support\IndianNumber::format($payment->amount) }}</td>
                            <td>{{ ucfirst($payment->payment_mode) }}</td>
                            @can('vendor-payments.manage')
                            <td>
                                <div class="d-flex gap-1 flex-wrap">
                                    <button type="button" class="btn btn-soft-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editPayment-{{ $payment->id }}" title="Edit" aria-label="Edit">
                                        <i class="ri-edit-line align-bottom"></i>
                                    </button>
                                    <form action="{{ route('admin.vendors.payments.destroy', [$vendor->id, $payment->id]) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-soft-danger btn-sm" data-confirm-delete title="Delete" aria-label="Delete">
                                            <i class="ri-delete-bin-fill align-bottom"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                            @endcan
                        </tr>
                        @empty
                        <tr><td colspan="5"><x-ui.empty-state icon="ri-money-dollar-circle-line" message="No payments recorded yet." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.data-table-card>
    </div>
</div>

@can('vendor-payments.manage')
@foreach($bills as $bill)
<div class="modal fade" id="editBill-{{ $bill->id }}" tabindex="-1" aria-labelledby="editBill-{{ $bill->id }}-label" aria-modal="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editBill-{{ $bill->id }}-label">Edit Bill</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('admin.vendors.bills.update', [$vendor->id, $bill->id]) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-2">
                        <label class="form-label" for="edit-bill-number-{{ $bill->id }}">Bill / Invoice Number</label>
                        <input type="text" class="form-control" id="edit-bill-number-{{ $bill->id }}" name="bill_number" value="{{ $bill->bill_number }}">
                    </div>
                    <div class="mb-2">
                        <label class="form-label" for="edit-bill-amount-{{ $bill->id }}">Amount <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0.01" class="form-control" id="edit-bill-amount-{{ $bill->id }}" name="amount" value="{{ $bill->amount }}" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" for="edit-bill-date-{{ $bill->id }}">Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="edit-bill-date-{{ $bill->id }}" name="date" value="{{ $bill->date->toDateString() }}" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" for="edit-bill-description-{{ $bill->id }}">Description</label>
                        <textarea class="form-control" id="edit-bill-description-{{ $bill->id }}" name="description">{{ $bill->description }}</textarea>
                    </div>
                    <div class="hstack gap-2 justify-content-end">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endforeach

@foreach($payments as $payment)
<div class="modal fade" id="editPayment-{{ $payment->id }}" tabindex="-1" aria-labelledby="editPayment-{{ $payment->id }}-label" aria-modal="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editPayment-{{ $payment->id }}-label">Edit Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('admin.vendors.payments.update', [$vendor->id, $payment->id]) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-2">
                        <label class="form-label" for="edit-payment-amount-{{ $payment->id }}">Amount <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0.01" class="form-control" id="edit-payment-amount-{{ $payment->id }}" name="amount" value="{{ $payment->amount }}" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" for="edit-payment-date-{{ $payment->id }}">Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="edit-payment-date-{{ $payment->id }}" name="date" value="{{ $payment->date->toDateString() }}" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" for="edit-payment-mode-{{ $payment->id }}">Payment Mode <span class="text-danger">*</span></label>
                        <select class="form-select" id="edit-payment-mode-{{ $payment->id }}" name="payment_mode" required>
                            <option value="cash" @selected($payment->payment_mode === 'cash')>Cash</option>
                            <option value="bank" @selected($payment->payment_mode === 'bank')>Bank Transfer</option>
                            <option value="upi" @selected($payment->payment_mode === 'upi')>UPI</option>
                            <option value="cheque" @selected($payment->payment_mode === 'cheque')>Cheque</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" for="edit-payment-description-{{ $payment->id }}">Description</label>
                        <textarea class="form-control" id="edit-payment-description-{{ $payment->id }}" name="description">{{ $payment->description }}</textarea>
                    </div>
                    <div class="hstack gap-2 justify-content-end">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endforeach

<x-ui.confirm-modal recordType="record" />
@endcan
@endsection
