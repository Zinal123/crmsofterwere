@extends('layouts.master')
@section('title')
Support Tickets
@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1')
Support Tickets
@endslot
@slot('title')
Support Tickets
@endslot
@endcomponent

<div class="row">
    <div class="col-lg-12">
        <x-ui.data-table-card title="Support Tickets">
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle">
                    <thead>
                        <tr><th>Client</th><th>Machine</th><th>Category</th><th>Problem</th><th>Priority</th><th>Status</th><th></th></tr>
                    </thead>
                    <tbody>
                        @forelse($tickets as $ticket)
                        <tr>
                            <td>{{ $ticket->clientAccount->name ?? '-' }}</td>
                            <td>{{ $ticket->clientMachine->product->name ?? '-' }} ({{ $ticket->clientMachine->serial_number ?? '-' }})</td>
                            <td>{{ ucfirst($ticket->problemType->category ?? '-') }}</td>
                            <td>{{ $ticket->problemType->name ?? '-' }}</td>
                            <td>
                                <x-ui.priority-badge :priority="$ticket->priority" />
                            </td>
                            <td>
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
                            </td>
                            <td>
                                <a href="{{ route('admin.tickets.show', $ticket->id) }}" class="btn btn-soft-success btn-sm" data-bs-toggle="tooltip" title="View" aria-label="View"><i class="ri-eye-line align-bottom"></i></a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7"><x-ui.empty-state icon="ri-file-list-3-line" message="No tickets raised yet." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.data-table-card>
    </div>
</div>
@endsection
