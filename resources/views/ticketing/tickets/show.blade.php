@extends('layouts.master')
@section('title')
Ticket #{{ $ticket->id }}
@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1')
Support Tickets
@endslot
@slot('title')
Ticket #{{ $ticket->id }}
@endslot
@endcomponent

<x-ui.back-link :route="route('admin.tickets.index')" label="Back to Tickets" />

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <h5 class="card-title">{{ $ticket->problemType->name ?? 'Ticket' }}</h5>
                    <div class="d-flex gap-1">
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
                <p class="text-muted mb-1">Category: {{ ucfirst($ticket->problemType->category ?? '-') }}</p>
                <p>{{ $ticket->description ?: 'No additional description provided.' }}</p>

                @if($ticket->photos->isNotEmpty())
                    <h6 class="mt-3">Photos</h6>
                    <div class="row g-2">
                        @foreach($ticket->photos as $photo)
                        <div class="col-6 col-md-3">
                            <img src="{{ asset('storage/' . $photo->path) }}" class="img-fluid rounded" alt="Ticket #{{ $ticket->id }} attachment {{ $loop->iteration }}">
                        </div>
                        @endforeach
                    </div>
                @endif

                @if($ticket->job)
                    <h6 class="mt-3">Linked Job</h6>
                    <a href="{{ route('jobs.show', $ticket->job->id) }}" class="btn btn-sm btn-outline-secondary">
                        <i class="ri-briefcase-4-line align-bottom"></i> View Job #{{ $ticket->job->id }}
                    </a>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Client & Machine</h5>
                <p class="mb-1"><strong>Client:</strong> {{ $ticket->clientAccount->name ?? '-' }}</p>
                <p class="mb-1"><strong>Phone:</strong> {{ $ticket->clientAccount->phone ?? '-' }}</p>
                <p class="mb-1"><strong>Product:</strong> {{ $ticket->clientMachine->product->name ?? '-' }}</p>
                <p class="mb-0"><strong>Serial No:</strong> {{ $ticket->clientMachine->serial_number ?? '-' }}</p>
            </div>
        </div>

        @can('tickets.assign')
        @if($ticket->status === 'open')
        <div class="card mt-3">
            <div class="card-body">
                <h5 class="card-title">Assign to Technician</h5>
                <form action="{{ route('admin.tickets.assign', $ticket->id) }}" method="POST">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label" for="ticket-worker">Worker <span class="text-danger">*</span></label>
                        <select id="ticket-worker" class="form-select" name="worker_id" required>
                            <option value="">-- Select worker --</option>
                            @foreach($workers as $worker)
                                <option value="{{ $worker->id }}">{{ $worker->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <x-ui.button type="submit" variant="success" icon="ri-user-shared-line">Assign &amp; Create Job</x-ui.button>
                </form>
            </div>
        </div>
        @endif
        @endcan
    </div>
</div>
@endsection
