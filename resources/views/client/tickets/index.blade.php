@extends('layouts.client')
@section('title')
My Tickets
@endsection
@section('content')
<x-ui.back-link :route="route('client.dashboard')" label="Back to My Machines" />

<h5 class="mb-3">My Tickets</h5>

@forelse($tickets as $ticket)
<a href="{{ route('client.tickets.show', $ticket->id) }}" class="text-decoration-none text-body">
    <div class="card mb-2">
        <div class="card-body d-flex justify-content-between align-items-start">
            <div>
                <h6 class="mb-1">{{ $ticket->problemType->name ?? 'Ticket' }}</h6>
                <p class="text-muted small mb-0">{{ $ticket->clientMachine->product->name ?? '-' }} ({{ $ticket->clientMachine->serial_number ?? '-' }})</p>
            </div>
            <div class="d-flex flex-column align-items-end gap-1">
                <x-ui.priority-badge :priority="$ticket->priority" />
                <x-ui.status-badge
                    :status="ucfirst(str_replace('_', ' ', $ticket->status))"
                    :variant="match($ticket->status) {
                        'resolved' => 'success',
                        'in_progress' => 'info',
                        'assigned' => 'warning',
                        default => 'danger',
                    }"
                    :icon="match($ticket->status) {
                        'resolved' => 'ri-checkbox-circle-line',
                        'in_progress' => 'ri-tools-line',
                        'assigned' => 'ri-user-follow-line',
                        default => 'ri-time-line',
                    }" />
            </div>
        </div>
    </div>
</a>
@empty
<x-ui.empty-state icon="ri-file-list-3-line" message="You haven't raised any tickets yet." />
@endforelse
@endsection
