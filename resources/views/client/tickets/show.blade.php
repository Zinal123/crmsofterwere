@extends('layouts.client')
@section('title')
Ticket #{{ $ticket->id }}
@endsection
@section('content')
<x-ui.back-link :route="route('client.tickets.index')" label="Back to My Tickets" />

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
            <h5 class="card-title">{{ $ticket->problemType->name ?? 'Ticket' }}</h5>
            <div class="d-flex gap-1">
                <x-ui.status-badge
                    :status="ucfirst($ticket->priority)"
                    :variant="match($ticket->priority) {
                        'urgent' => 'danger',
                        'high' => 'warning',
                        'low' => 'light',
                        default => 'secondary',
                    }"
                    :icon="match($ticket->priority) {
                        'urgent' => 'ri-alarm-warning-line',
                        'high' => 'ri-arrow-up-circle-line',
                        'low' => 'ri-arrow-down-circle-line',
                        default => 'ri-subtract-line',
                    }" />
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
        <p class="text-muted mb-1">{{ $ticket->clientMachine->product->name ?? '-' }} (SN: {{ $ticket->clientMachine->serial_number ?? '-' }})</p>
        <p class="text-muted mb-3">Category: {{ ucfirst($ticket->problemType->category ?? '-') }}</p>
        <p>{{ $ticket->description ?: 'No additional description provided.' }}</p>

        @if($ticket->photos->isNotEmpty())
            <h6 class="mt-3">Photos</h6>
            <div class="row g-2">
                @foreach($ticket->photos as $photo)
                <div class="col-6 col-md-4">
                    <img src="{{ asset('storage/' . $photo->path) }}" class="img-fluid rounded" alt="Ticket #{{ $ticket->id }} attachment {{ $loop->iteration }}">
                </div>
                @endforeach
            </div>
        @endif

        <p class="text-muted small mt-3 mb-0">Raised on {{ $ticket->created_at->format('d M Y, H:i') }}</p>
    </div>
</div>
@endsection
