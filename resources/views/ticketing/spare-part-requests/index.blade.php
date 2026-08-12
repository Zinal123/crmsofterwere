@extends('layouts.master')
@section('title')
Spare Part Requests
@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1')
Spare Part Requests
@endslot
@slot('title')
Spare Part Requests
@endslot
@endcomponent

<div class="row">
    <div class="col-lg-12">
        <x-ui.data-table-card title="Spare Part Requests">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr><th>Client</th><th>Machine</th><th>Part</th><th>Qty</th><th>Note</th><th>Status</th>@can('spare-part-requests.manage')<th></th>@endcan</tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $request)
                        <tr>
                            <td>{{ $request->clientAccount->name ?? '-' }}</td>
                            <td>{{ $request->clientMachine->product->name ?? '-' }} ({{ $request->clientMachine->serial_number ?? '-' }})</td>
                            <td>{{ $request->product->name ?? '-' }}</td>
                            <td>
                                {{ $request->quantity }}
                                @if($request->status === 'pending' || $request->status === 'approved')
                                    <x-ui.status-badge
                                        :status="$request->is_available ? 'Available' : 'Not Available'"
                                        :variant="$request->is_available ? 'success' : 'danger'"
                                        :icon="$request->is_available ? 'ri-checkbox-circle-line' : 'ri-error-warning-line'" />
                                @endif
                            </td>
                            <td>{{ $request->note ?: '-' }}</td>
                            <td>
                                <x-ui.status-badge
                                    :status="ucfirst($request->status)"
                                    :variant="match($request->status) {
                                        'fulfilled' => 'success',
                                        'approved' => 'info',
                                        'rejected' => 'danger',
                                        default => 'warning',
                                    }"
                                    :icon="match($request->status) {
                                        'fulfilled' => 'ri-checkbox-circle-line',
                                        'approved' => 'ri-thumb-up-line',
                                        'rejected' => 'ri-close-circle-line',
                                        default => 'ri-time-line',
                                    }" />
                            </td>
                            @can('spare-part-requests.manage')
                            <td>
                                <form action="{{ route('admin.spare-part-requests.update-status', $request->id) }}" method="POST" class="d-flex gap-1">
                                    @csrf
                                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                        <option value="pending" @selected($request->status === 'pending')>Pending</option>
                                        <option value="approved" @selected($request->status === 'approved')>Approved</option>
                                        <option value="fulfilled" @selected($request->status === 'fulfilled')>Fulfilled</option>
                                        <option value="rejected" @selected($request->status === 'rejected')>Rejected</option>
                                    </select>
                                </form>
                            </td>
                            @endcan
                        </tr>
                        @empty
                        <tr><td colspan="7"><x-ui.empty-state icon="ri-tools-fill" message="No spare part requests yet." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.data-table-card>
    </div>
</div>
@endsection
