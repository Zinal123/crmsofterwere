@extends('layouts.client')
@section('title')
My Machines
@endsection
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h5 class="mb-0">My Machines</h5>
    <div class="d-flex gap-2">
        <a href="{{ route('client.tickets.index') }}" class="btn btn-sm btn-outline-secondary"><i class="ri-file-list-3-line align-bottom"></i> My Tickets</a>
        <a href="{{ route('client.spare-parts.index') }}" class="btn btn-sm btn-outline-secondary"><i class="ri-tools-fill align-bottom"></i> My Spare Part Requests</a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-4">
        <a href="{{ route('client.tickets.index') }}" class="text-reset">
            <div class="card mb-0 h-100">
                <div class="card-body text-center py-3">
                    <i class="ri-customer-service-2-line fs-3 text-primary"></i>
                    <h3 class="my-1 tabular-nums">{{ $openTickets }}</h3>
                    <p class="text-muted mb-0 small">Open Tickets</p>
                </div>
            </div>
        </a>
    </div>
    <div class="col-4">
        <a href="{{ route('client.spare-parts.index') }}" class="text-reset">
            <div class="card mb-0 h-100">
                <div class="card-body text-center py-3">
                    <i class="ri-tools-fill fs-3 text-warning"></i>
                    <h3 class="my-1 tabular-nums">{{ $openSpareParts }}</h3>
                    <p class="text-muted mb-0 small">Spare Part Requests</p>
                </div>
            </div>
        </a>
    </div>
    <div class="col-4">
        <div class="card mb-0 h-100 {{ $amountDue > 0 ? 'border border-danger border-opacity-25' : '' }}">
            <div class="card-body text-center py-3">
                <i class="ri-wallet-3-line fs-3 {{ $amountDue > 0 ? 'text-danger' : 'text-success' }}"></i>
                <h3 class="my-1 tabular-nums {{ $amountDue > 0 ? 'text-danger' : 'text-success' }}">&#8377;{{ \App\Support\IndianNumber::format($amountDue) }}</h3>
                <p class="text-muted mb-0 small">{{ $amountDue > 0 ? 'Amount Due' : 'All Paid' }}</p>
            </div>
        </div>
    </div>
</div>

@forelse($machines as $machine)
<div class="card mb-3">
    <div class="card-body">
        <h5 class="card-title mb-1">{{ $machine->product->name ?? 'Machine' }}</h5>
        <p class="text-muted mb-2">Serial No: {{ $machine->serial_number }}</p>
        @if($machine->installed_at)
            <p class="text-muted small mb-3">Installed: {{ $machine->installed_at->format('d M Y') }}</p>
        @endif

        @if($machine->invoice)
            @php
                $due = (float) $machine->invoice->remaining_amount;
            @endphp
            <div class="border rounded p-2 mb-3 {{ $due > 0 ? 'bg-danger-subtle' : 'bg-success-subtle' }}">
                <div class="d-flex justify-content-between small">
                    <span>Invoice Amount</span>
                    <span>&#8377;{{ \App\Support\IndianNumber::format($machine->invoice->amount) }}</span>
                </div>
                <div class="d-flex justify-content-between small">
                    <span>Paid</span>
                    <span>&#8377;{{ \App\Support\IndianNumber::format($machine->invoice->paidamount) }}</span>
                </div>
                <div class="d-flex justify-content-between fw-semibold {{ $due > 0 ? 'text-danger' : 'text-success' }}">
                    <span>{{ $due > 0 ? 'Amount Due' : 'Fully Paid' }}</span>
                    <span>&#8377;{{ \App\Support\IndianNumber::format($machine->invoice->remaining_amount) }}</span>
                </div>
            </div>
        @endif

        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('client.tickets.create', $machine->id) }}" class="btn btn-danger btn-sm">
                <i class="ri-error-warning-line align-bottom"></i> Report a Problem
            </a>
            <a href="{{ route('client.spare-parts.create', $machine->id) }}" class="btn btn-outline-secondary btn-sm">
                <i class="ri-tools-fill align-bottom"></i> Request Spare Part
            </a>
        </div>
    </div>
</div>
@empty
<x-ui.empty-state icon="ri-cpu-line" message="No machines are registered to your account yet. Please contact us if this looks wrong." />
@endforelse
@endsection
