@extends('layouts.master')
@section('title') Tally Sync @endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Integration @endslot
@slot('title') Tally Sync @endslot
@endcomponent

<x-ui.data-table-card title="Tally Sync Queue">
    <p class="text-muted small mb-3">Invoices queued to post into Tally Prime as Sales vouchers. Failed rows can be retried once the underlying issue (e.g. a missing ledger in Tally) is fixed.</p>

    @if ($notConnected)
    <div class="alert alert-warning">
        <i class="ri-error-warning-line align-middle me-1"></i>
        Tally is not connected &mdash; <a href="{{ route('tally-connection.edit') }}">check the connection settings</a> before invoices can sync.
    </div>
    @endif

    <div class="table-responsive">
        <table class="table table-bordered align-middle mb-0">
            <thead>
                <tr>
                    <th>Reference</th>
                    <th>Voucher Type</th>
                    <th>Status</th>
                    <th>Attempts</th>
                    <th>Last Error</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($queue as $item)
                <tr>
                    <td>{{ $item->reference_no }}</td>
                    <td class="text-capitalize">{{ $item->voucher_type }}</td>
                    <td>
                        @php
                            $badge = $item->status === 'synced' ? 'success' : ($item->status === 'failed' ? 'danger' : 'warning');
                        @endphp
                        <span class="badge bg-{{ $badge }}-subtle text-{{ $badge }}">{{ ucfirst($item->status) }}</span>
                    </td>
                    <td>{{ $item->attempts }}</td>
                    <td>{{ $item->last_error }}</td>
                    <td>
                        @if ($item->status === 'failed')
                        <form method="POST" action="{{ route('tally-sync.retry', $item) }}">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-soft-primary">Retry</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted">No Tally sync activity yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $queue->links() }}
</x-ui.data-table-card>
@endsection
