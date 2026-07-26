@extends('layouts.client')
@section('title')
My Spare Part Requests
@endsection
@section('content')
<x-ui.back-link :route="route('client.dashboard')" label="Back to My Machines" />

<h5 class="mb-3">My Spare Part Requests</h5>

@forelse($requests as $request)
<div class="card mb-2">
    <div class="card-body d-flex justify-content-between align-items-start">
        <div>
            <h6 class="mb-1">{{ $request->product->name ?? 'Part' }} &times; {{ $request->quantity }}</h6>
            <p class="text-muted small mb-0">{{ $request->clientMachine->product->name ?? '-' }} ({{ $request->clientMachine->serial_number ?? '-' }})</p>
        </div>
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
    </div>
</div>
@empty
<x-ui.empty-state icon="ri-tools-fill" message="You haven't requested any spare parts yet." />
@endforelse
@endsection
