@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <x-ui.data-table-card title="Pending Approval">
        @forelse($jobs as $job)
            <div class="card border mb-2">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <a href="{{ route('jobs.show', $job->id) }}">{{ $job->title }}</a>
                        <p class="text-muted mb-0 small">Requested by {{ $job->creator->name }} &middot; {{ $job->machine->name ?? $job->site_name }}</p>
                    </div>
                    <div class="d-flex gap-2">
                        <form action="{{ route('jobs.approve', $job->id) }}" method="POST">
                            @csrf
                            <x-ui.button variant="success" :soft="true" type="submit" icon="ri-checkbox-circle-line" ariaLabel="Approve job" title="Approve" data-bs-toggle="tooltip" />
                        </form>
                        <button type="button" class="btn btn-soft-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $job->id }}" title="Reject" aria-label="Reject">
                            <i class="ri-close-circle-line align-bottom"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="rejectModal{{ $job->id }}" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="{{ route('jobs.reject', $job->id) }}" method="POST">
                            @csrf
                            <div class="modal-header"><h5 class="modal-title">Reject "{{ $job->title }}"</h5></div>
                            <div class="modal-body">
                                <label class="form-label" for="rejection-reason-{{ $job->id }}">Reason <span class="text-danger">*</span></label>
                                <textarea id="rejection-reason-{{ $job->id }}" name="rejection_reason" class="form-control" required></textarea>
                            </div>
                            <div class="modal-footer">
                                <x-ui.button variant="secondary" type="button" data-bs-dismiss="modal">Cancel</x-ui.button>
                                <x-ui.button variant="danger" type="submit" icon="ri-close-circle-line">Confirm Reject</x-ui.button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <x-ui.empty-state icon="ri-checkbox-circle-line" message="No jobs waiting for approval." />
        @endforelse
    </x-ui.data-table-card>
</div>
@endsection
