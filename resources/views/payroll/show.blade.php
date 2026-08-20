@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <x-ui.back-link :route="route('employees.index')" label="Back to Employees" />
    @php
        $prevMonth = $month - 1; $prevYear = $year;
        if ($prevMonth < 1) { $prevMonth = 12; $prevYear--; }
        $nextMonth = $month + 1; $nextYear = $year;
        if ($nextMonth > 12) { $nextMonth = 1; $nextYear++; }
    @endphp
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h5 class="card-title mb-0">{{ $employee->name }} — Payroll ({{ $year }}-{{ str_pad($month, 2, '0', STR_PAD_LEFT) }})</h5>
                <div class="d-flex gap-1">
                    <a href="{{ route('attendance.register', ['employee' => $employee->id, 'year' => $year, 'month' => $month]) }}" class="btn btn-sm btn-outline-secondary"><i class="ri-calendar-check-line align-bottom"></i> View Attendance</a>
                    <a href="{{ route('employees.payroll', ['employee' => $employee->id, 'year' => $prevYear, 'month' => $prevMonth]) }}" class="btn btn-sm btn-outline-secondary"><i class="ri-arrow-left-s-line align-bottom"></i> Previous Month</a>
                    <a href="{{ route('employees.payroll', ['employee' => $employee->id, 'year' => $nextYear, 'month' => $nextMonth]) }}" class="btn btn-sm btn-outline-secondary">Next Month <i class="ri-arrow-right-s-line align-bottom"></i></a>
                </div>
            </div>
            <div class="row g-2">
                <div class="col-md-3"><p class="text-muted mb-0">Days Present</p><h5>{{ $earnings['days_present'] }}</h5></div>
                <div class="col-md-3"><p class="text-muted mb-0">Half Days</p><h5>{{ $earnings['days_half'] }}</h5></div>
                <div class="col-md-3"><p class="text-muted mb-0">Leave</p><h5>{{ $earnings['days_leave'] }}</h5></div>
                <div class="col-md-3"><p class="text-muted mb-0">Absent</p><h5>{{ $earnings['days_absent'] }}</h5></div>
                <div class="col-md-3"><p class="text-muted mb-0">Day Rate (₹)</p><h5 class="tabular-nums">{{ \App\Support\IndianNumber::format($earnings['day_rate']) }}</h5></div>
                <div class="col-md-3"><p class="text-muted mb-0">Base Earned (₹)</p><h5 class="tabular-nums">{{ \App\Support\IndianNumber::format($earnings['base_earned']) }}</h5></div>
                <div class="col-md-3"><p class="text-muted mb-0">Overtime Earned (₹)</p><h5 class="tabular-nums">{{ \App\Support\IndianNumber::format($earnings['overtime_earned']) }}</h5></div>
                <div class="col-md-3"><p class="text-muted mb-0">Total Earned (₹)</p><h5 class="tabular-nums">{{ \App\Support\IndianNumber::format($earnings['total_earned']) }}</h5></div>
                <div class="col-md-3"><p class="text-muted mb-0">Total Paid (₹)</p><h5 class="tabular-nums">{{ \App\Support\IndianNumber::format($earnings['total_paid']) }}</h5></div>
                <div class="col-md-3"><p class="text-muted mb-0">Balance Due (₹)</p><h4 class="text-danger tabular-nums">{{ \App\Support\IndianNumber::format($earnings['balance_due']) }}</h4></div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-body">
            <h5 class="card-title">Payments This Month</h5>
            <div class="table-responsive">
            <table class="table table-bordered tabular-nums">
                <thead><tr><th>Date</th><th>Amount</th><th>Note</th><th></th></tr></thead>
                <tbody>
                    @forelse($payments as $payment)
                        <tr>
                            <td>{{ $payment->date->toDateString() }}</td>
                            <td>{{ \App\Support\IndianNumber::format($payment->amount) }}</td>
                            <td>{{ $payment->note }}</td>
                            <td>
                                <div class="d-flex gap-1 flex-wrap">
                                    @can('payroll.manage-payments')
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editPayment-{{ $payment->id }}"><i class="ri-edit-line align-bottom"></i> Edit</button>
                                    <form action="{{ route('employees.payments.destroy', [$employee->id, $payment->id]) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" data-confirm-delete><i class="ri-delete-bin-line align-bottom"></i> Delete</button>
                                    </form>
                                    @endcan
                                    @can('payroll.view-audit')
                                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#auditTrailModal-salary_payment" data-audit-id="{{ $payment->id }}"><i class="ri-history-line align-bottom"></i> History</button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4"><x-ui.empty-state icon="ri-money-rupee-circle-line" message="No payments recorded this month yet." /></td></tr>
                    @endforelse
                </tbody>
            </table>
            </div>

            @can('payroll.manage-payments')
            @foreach($payments as $payment)
            <div class="modal fade" id="editPayment-{{ $payment->id }}" tabindex="-1" aria-labelledby="editPayment-{{ $payment->id }}-label" aria-modal="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editPayment-{{ $payment->id }}-label">Edit Payment</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form action="{{ route('employees.payments.update', [$employee->id, $payment->id]) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="mb-2">
                                    <label class="form-label" for="edit-payment-date-{{ $payment->id }}">Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="edit-payment-date-{{ $payment->id }}" name="date" value="{{ $payment->date->toDateString() }}" required>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label" for="edit-payment-amount-{{ $payment->id }}">Amount (₹) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" class="form-control" id="edit-payment-amount-{{ $payment->id }}" name="amount" value="{{ $payment->amount }}" required>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label" for="edit-payment-note-{{ $payment->id }}">Note</label>
                                    <input type="text" class="form-control" id="edit-payment-note-{{ $payment->id }}" name="note" value="{{ $payment->note }}">
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
            @endcan

            <form action="{{ route('employees.payments.store', $employee->id) }}" method="POST" class="row g-2 align-items-end mt-1">
                @csrf
                <div class="col-md-3">
                    <label class="form-label" for="payment-date">Date <span class="text-danger">*</span></label>
                    <input id="payment-date" type="date" name="date" class="form-control" value="{{ now()->toDateString() }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="payment-amount">Amount (₹) <span class="text-danger">*</span></label>
                    <input id="payment-amount" type="number" step="0.01" name="amount" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="payment-note">Note</label>
                    <input id="payment-note" type="text" name="note" class="form-control">
                </div>
                <div class="col-md-2">
                    <x-ui.button variant="success" type="submit" icon="ri-add-line" ariaLabel="Record payment">Record Payment</x-ui.button>
                </div>
            </form>
        </div>
    </div>
</div>

@can('payroll.view-audit')
    <x-ui.audit-trail-modal type="salary_payment" />
@endcan
@can('payroll.manage-payments')
    <x-ui.confirm-modal recordType="payment" />
@endcan
@endsection
