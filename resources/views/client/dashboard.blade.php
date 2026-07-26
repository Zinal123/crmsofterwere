@extends('layouts.client')
@section('title')
My Machines
@endsection
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">My Machines</h5>
    <a href="{{ route('client.tickets.index') }}" class="btn btn-sm btn-outline-secondary"><i class="ri-file-list-3-line align-bottom"></i> My Tickets</a>
</div>

@forelse($machines as $machine)
<div class="card mb-3">
    <div class="card-body">
        <h5 class="card-title mb-1">{{ $machine->product->name ?? 'Machine' }}</h5>
        <p class="text-muted mb-2">Serial No: {{ $machine->serial_number }}</p>
        @if($machine->installed_at)
            <p class="text-muted small mb-3">Installed: {{ $machine->installed_at->format('d M Y') }}</p>
        @endif
        <a href="{{ route('client.tickets.create', $machine->id) }}" class="btn btn-danger btn-sm">
            <i class="ri-error-warning-line align-bottom"></i> Report a Problem
        </a>
    </div>
</div>
@empty
<x-ui.empty-state icon="ri-cpu-line" message="No machines are registered to your account yet. Please contact us if this looks wrong." />
@endforelse
@endsection
