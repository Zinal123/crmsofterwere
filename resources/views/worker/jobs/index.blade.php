@extends('layouts.worker')

@section('title', 'My Jobs')

@section('content')
<h4 class="mb-3">My Jobs</h4>

<div class="d-flex flex-column gap-3">
    @forelse($jobs as $job)
        <a href="{{ route('jobs.show', $job->id) }}" class="text-decoration-none text-body job-tap-card">
            <div class="card border">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <h5 class="card-title mb-1">{{ $job->title }}</h5>
                        <div class="d-flex flex-column gap-1 align-items-end">
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
                    <p class="text-muted mb-0">{{ $job->machine->name ?? $job->site_name }}</p>
                    @if($job->status === 'rejected')
                        <p class="text-danger small mb-0">{{ $job->rejection_reason }}</p>
                    @endif
                </div>
            </div>
        </a>
    @empty
        <x-ui.empty-state icon="ri-briefcase-line" message="No jobs assigned to you yet." />
    @endforelse
</div>

<a href="{{ route('jobs.create') }}" class="btn btn-primary btn-shopfloor w-100 mt-4">
    <i class="ri-add-line align-bottom me-1"></i> Request / Assign Job
</a>
@endsection
