@extends('layouts.worker')

@section('title', 'My Jobs')

@section('content')
<h4 class="mb-3">My Jobs</h4>

@if($machines->isNotEmpty())
    <button type="button" class="btn btn-danger btn-shopfloor w-100 mb-3" data-bs-toggle="modal" data-bs-target="#flagMachineDownModal">
        <i class="ri-alarm-warning-line align-bottom me-1"></i> Flag Machine Down
    </button>
@endif

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

@if($machines->isNotEmpty())
<div class="modal fade" id="flagMachineDownModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('machines.flag-down', 0) }}" method="POST" id="flag-machine-down-form">
                @csrf
                <div class="modal-header"><h5 class="modal-title">Flag Machine Down</h5></div>
                <div class="modal-body">
                    <label class="form-label" for="flag-down-machine">Machine <span class="text-danger">*</span></label>
                    <select id="flag-down-machine" class="form-select mb-3" required onchange="document.getElementById('flag-machine-down-form').action = '{{ url('machines') }}/' + this.value + '/flag-down';">
                        <option value="">-- Select a machine --</option>
                        @foreach($machines as $machine)
                            <option value="{{ $machine->id }}">{{ $machine->name }}</option>
                        @endforeach
                    </select>
                    <label class="form-label" for="flag-down-note">What's wrong?</label>
                    <textarea id="flag-down-note" name="note" class="form-control" rows="3" placeholder="Optional - describe what you're seeing"></textarea>
                </div>
                <div class="modal-footer">
                    <x-ui.button variant="secondary" type="button" data-bs-dismiss="modal">Cancel</x-ui.button>
                    <x-ui.button variant="danger" type="submit" icon="ri-alarm-warning-line" class="btn-shopfloor">Flag Down</x-ui.button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
