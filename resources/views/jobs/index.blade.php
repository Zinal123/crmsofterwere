@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <x-ui.data-table-card title="{{ auth()->user()->can('jobs.view-all') ? 'All Jobs' : 'My Jobs' }}"
        create-route="{{ route('jobs.create') }}" create-label="Request / Assign Job">
        <div class="row g-2">
            @forelse($jobs as $job)
                <div class="col-12 col-md-6 col-lg-4">
                    {{-- This card's onclick is a mouse/touch-only convenience layer on top of
                         the real, fully keyboard/screen-reader accessible <a> title link below -
                         not a replacement for it, so it's deliberately not given an interactive
                         ARIA role (that would just create a second, redundant tab stop for the
                         same destination, and still couldn't be a real <a> since it wraps other
                         interactive children like the status badges and dropdown). --}}
                    <div class="card border job-tap-card" style="cursor: pointer;" onclick="if (!event.target.closest('a')) { window.location = '{{ route('jobs.show', $job->id) }}'; }">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <h5 class="card-title mb-1"><a href="{{ route('jobs.show', $job->id) }}" class="text-decoration-none text-body">{{ $job->title }}</a></h5>
                                    <div class="d-flex gap-1">
                                        @if($job->overdue_flagged_at)
                                            <x-ui.status-badge status="Overdue" variant="danger" icon="ri-alarm-warning-line" />
                                        @endif
                                        <x-ui.status-badge
                                            :status="ucfirst(str_replace('_', ' ', $job->status))"
                                            :variant="match($job->status) {
                                                'completed' => 'success',
                                                'rejected' => 'danger',
                                                'on_hold' => 'warning',
                                                default => 'info',
                                            }"
                                            :icon="match($job->status) {
                                                'completed' => 'ri-checkbox-circle-line',
                                                'rejected' => 'ri-close-circle-line',
                                                'on_hold' => 'ri-pause-circle-line',
                                                default => 'ri-time-line',
                                            }" />
                                    </div>
                                </div>
                                <p class="text-muted mb-1">
                                    @can('jobs.manage-machines')
                                        @if($job->machine)
                                            <a href="{{ route('machines.index') . '#machine-' . $job->machine->id }}">{{ $job->machine->name }}</a>
                                        @else
                                            {{ $job->site_name }}
                                        @endif
                                    @else
                                        {{ $job->machine->name ?? $job->site_name }}
                                    @endcan
                                </p>
                                @if(isset($materialStatus[$job->id]))
                                    @if($materialStatus[$job->id])
                                        <span class="badge bg-success-subtle text-success"><i class="ri-checkbox-circle-line align-middle"></i> Parts ready</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger"><i class="ri-alert-line align-middle"></i> Shortage</span>
                                    @endif
                                @endif
                                @if($job->status === 'rejected')
                                    <p class="text-danger small mb-0">{{ $job->rejection_reason }}</p>
                                @endif
                            </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <x-ui.empty-state icon="ri-briefcase-line" message="No jobs yet." />
                </div>
            @endforelse
        </div>
    </x-ui.data-table-card>
</div>
@endsection
