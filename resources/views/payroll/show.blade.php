@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">{{ $employee->name }} — Payroll ({{ $year }}-{{ str_pad($month, 2, '0', STR_PAD_LEFT) }})</h5>
            <div class="row g-2">
                <div class="col-md-3"><p class="text-muted mb-0">Days Present</p><h5>{{ $earnings['days_present'] }}</h5></div>
                <div class="col-md-3"><p class="text-muted mb-0">Half Days</p><h5>{{ $earnings['days_half'] }}</h5></div>
                <div class="col-md-3"><p class="text-muted mb-0">Leave</p><h5>{{ $earnings['days_leave'] }}</h5></div>
                <div class="col-md-3"><p class="text-muted mb-0">Absent</p><h5>{{ $earnings['days_absent'] }}</h5></div>
                <div class="col-md-3"><p class="text-muted mb-0">Day Rate (₹)</p><h5>{{ $earnings['day_rate'] }}</h5></div>
                <div class="col-md-3"><p class="text-muted mb-0">Base Earned (₹)</p><h5>{{ $earnings['base_earned'] }}</h5></div>
                <div class="col-md-3"><p class="text-muted mb-0">Overtime Earned (₹)</p><h5>{{ $earnings['overtime_earned'] }}</h5></div>
                <div class="col-md-3"><p class="text-muted mb-0">Total Earned (₹)</p><h5>{{ $earnings['total_earned'] }}</h5></div>
                <div class="col-md-3"><p class="text-muted mb-0">Total Paid (₹)</p><h5>{{ $earnings['total_paid'] }}</h5></div>
                <div class="col-md-3"><p class="text-muted mb-0">Balance Due (₹)</p><h4 class="text-danger">{{ $earnings['balance_due'] }}</h4></div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-body">
            <h5 class="card-title">Payments This Month</h5>
            <table class="table table-bordered">
                <thead><tr><th>Date</th><th>Amount</th><th>Note</th></tr></thead>
                <tbody>
                    @forelse($payments as $payment)
                        <tr>
                            <td>{{ $payment->date->toDateString() }}</td>
                            <td>{{ $payment->amount }}</td>
                            <td>{{ $payment->note }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3"><x-ui.empty-state icon="ri-money-rupee-circle-line" message="No payments recorded this month yet." /></td></tr>
                    @endforelse
                </tbody>
            </table>

            <form action="{{ route('employees.payments.store', $employee->id) }}" method="POST" class="d-flex gap-2 flex-wrap align-items-end mt-3">
                @csrf
                <div>
                    <label class="form-label" for="payment-date">Date</label>
                    <input id="payment-date" type="date" name="date" class="form-control" value="{{ now()->toDateString() }}" required>
                </div>
                <div>
                    <label class="form-label" for="payment-amount">Amount (₹)</label>
                    <input id="payment-amount" type="number" step="0.01" name="amount" class="form-control" required>
                </div>
                <div>
                    <label class="form-label" for="payment-note">Note</label>
                    <input id="payment-note" type="text" name="note" class="form-control">
                </div>
                <x-ui.button variant="success" type="submit" icon="ri-add-line" ariaLabel="Record payment">Record Payment</x-ui.button>
            </form>
        </div>
    </div>
</div>
@endsection
