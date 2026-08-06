<div class="container-fluid">
    <x-ui.back-link :route="route('jobs.index')" label="Back to Jobs" />
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
                <h4>{{ $job->title }}</h4>
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
            <p>{{ $job->description }}</p>
            <p class="text-muted">{{ $job->machine->name ?? $job->site_name }} &middot; Priority: {{ ucfirst($job->priority) }}</p>

            @if($job->checklistItems->isNotEmpty() || $job->created_by === auth()->id() || auth()->user()->can('jobs.assign'))
                <div class="card border mb-3">
                    <div class="card-body">
                        <h6>Work Instructions</h6>
                        @forelse($job->checklistItems as $checklistItem)
                            <form action="{{ route('jobs.checklist.toggle', [$job->id, $checklistItem->id]) }}" method="POST" class="job-action-form">
                                @csrf
                                <div class="form-check py-1">
                                    <input class="form-check-input" type="checkbox" id="checklist-item-{{ $checklistItem->id }}" onchange="this.form.submit()" @checked($checklistItem->is_completed) style="width: 1.5em; height: 1.5em;">
                                    <label class="form-check-label {{ $checklistItem->is_completed ? 'text-decoration-line-through text-muted' : '' }}" for="checklist-item-{{ $checklistItem->id }}">
                                        {{ $checklistItem->description }}
                                    </label>
                                    @if($checklistItem->is_completed && $checklistItem->completer)
                                        <span class="small text-muted d-block ms-4">Checked by {{ $checklistItem->completer->name }}, {{ $checklistItem->completed_at->format('d M Y, H:i') }}</span>
                                    @endif
                                </div>
                            </form>
                        @empty
                            <p class="text-muted small mb-2">No work instructions added yet.</p>
                        @endforelse

                        @if($job->created_by === auth()->id() || auth()->user()->can('jobs.assign'))
                            <form action="{{ route('jobs.checklist.store', $job->id) }}" method="POST" class="d-flex gap-2 mt-2">
                                @csrf
                                <label for="new-checklist-item" class="visually-hidden">New work instruction</label>
                                <input type="text" id="new-checklist-item" name="description" class="form-control form-control-sm" placeholder="Add a work instruction or checklist step" required maxlength="255">
                                <x-ui.button variant="secondary" size="sm" type="submit" icon="ri-add-line" ariaLabel="Add checklist item" />
                            </form>
                        @endif
                    </div>
                </div>
            @endif

            @if($job->status === 'completed')
                <a href="{{ route('jobs.pdf', $job->id) }}" class="btn btn-soft-secondary btn-sm mb-2">
                    <i class="ri-file-pdf-2-line align-bottom me-1"></i> Download Completion Report
                </a>
            @endif

            @if($job->status === 'rejected')
                <div class="alert alert-danger">Rejected: {{ $job->rejection_reason }}</div>
            @endif
            @if($job->status === 'on_hold')
                <div class="alert alert-warning">On hold: {{ $job->on_hold_reason }}</div>
            @endif

            @if($job->assigned_to === auth()->id())
                <div class="d-flex gap-2 flex-wrap my-3">
                    @if($job->status === 'assigned')
                        <form action="{{ route('jobs.start', $job->id) }}" method="POST" class="job-action-form">
                            @csrf
                            <x-ui.button variant="success" type="submit" icon="ri-play-circle-line" ariaLabel="Start job" class="btn-shopfloor">Start</x-ui.button>
                        </form>
                    @endif
                    @if($job->status === 'in_progress')
                        <button type="button" class="btn btn-warning btn-shopfloor" data-bs-toggle="modal" data-bs-target="#holdJobModal">
                            <i class="ri-pause-circle-line"></i> Put On Hold
                        </button>
                    @endif
                    @if($job->status === 'on_hold')
                        <form action="{{ route('jobs.resume', $job->id) }}" method="POST" class="job-action-form">
                            @csrf
                            <x-ui.button variant="success" type="submit" icon="ri-play-circle-line" ariaLabel="Resume job" class="btn-shopfloor">Resume</x-ui.button>
                        </form>
                    @endif
                </div>

                @if($job->status === 'in_progress')
                    <div class="card border">
                        <div class="card-body">
                            <h6>Add Proof Photo <span class="text-danger">*</span></h6>
                            <form id="photo-upload-form" class="job-action-form" action="{{ route('jobs.photos.store', $job->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <input type="file" name="photo" accept="image/*" capture="environment" required class="form-control mb-2">
                                <input type="hidden" name="latitude" id="photo-lat">
                                <input type="hidden" name="longitude" id="photo-lng">
                                <p id="location-status" class="small text-muted">Checking location…</p>
                                <x-ui.button variant="primary" type="submit" icon="ri-upload-line" ariaLabel="Upload photo" class="btn-shopfloor">Upload</x-ui.button>
                            </form>
                        </div>
                    </div>

                    @if($job->photos->isNotEmpty())
                        @php
                            $jobLabel = $job->title . ' on ' . ($job->machine->name ?? $job->site_name);
                            $completeConfirmMessage = "Complete job \"{$jobLabel}\"? This will close the job and cannot be undone.";
                        @endphp
                        <form action="{{ route('jobs.complete', $job->id) }}" method="POST" class="mt-3 job-action-form"
                            onsubmit="return confirm({{ \Illuminate\Support\Js::from($completeConfirmMessage) }});">
                            @csrf
                            <label class="form-label" for="completion-notes">Completion Notes <span class="text-danger">*</span></label>
                            <textarea id="completion-notes" name="completion_notes" class="form-control mb-2" required></textarea>
                            <x-ui.button variant="success" type="submit" icon="ri-checkbox-circle-line" ariaLabel="Complete job" class="btn-shopfloor">Complete Job</x-ui.button>
                        </form>
                    @endif
                @endif
            @endif

            @can('jobs.approve')
                @if($job->status === 'pending_approval')
                    <div class="d-flex gap-2 my-3">
                        <form action="{{ route('jobs.approve', $job->id) }}" method="POST">
                            @csrf
                            <x-ui.button variant="success" type="submit" icon="ri-checkbox-circle-line" ariaLabel="Approve job">Approve</x-ui.button>
                        </form>
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectJobModal">
                            <i class="ri-close-circle-line"></i> Reject
                        </button>
                    </div>
                    <div class="modal fade" id="rejectJobModal" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form action="{{ route('jobs.reject', $job->id) }}" method="POST">
                                    @csrf
                                    <div class="modal-header"><h5 class="modal-title">Reject Job</h5></div>
                                    <div class="modal-body">
                                        <label class="form-label" for="reject-reason">Reason <span class="text-danger">*</span></label>
                                        <textarea id="reject-reason" name="rejection_reason" class="form-control" required></textarea>
                                    </div>
                                    <div class="modal-footer">
                                        <x-ui.button variant="secondary" type="button" data-bs-dismiss="modal">Cancel</x-ui.button>
                                        <x-ui.button variant="danger" type="submit" icon="ri-close-circle-line">Confirm Reject</x-ui.button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif
            @endcan

            @can('jobs.assign')
                @if(in_array($job->status, ['assigned', 'in_progress', 'on_hold']))
                    <form action="{{ route('jobs.reassign', $job->id) }}" method="POST" class="d-flex gap-2 align-items-end my-3">
                        @csrf
                        <div>
                            <label class="form-label" for="reassign-to">Reassign to <span class="text-danger">*</span></label>
                            <select id="reassign-to" name="assigned_to" class="form-select">
                                @foreach($workers as $worker)
                                    <option value="{{ $worker->id }}" @selected($worker->id === $job->assigned_to)>{{ $worker->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <x-ui.button variant="secondary" type="submit" icon="ri-user-shared-line" ariaLabel="Reassign job">Reassign</x-ui.button>
                    </form>
                @endif
            @endcan

            @can('jobs.view-all')
                <h6 class="mt-4">History</h6>
                <ul class="list-group">
                    @foreach($job->auditLogs()->orderBy('created_at')->get() as $log)
                        <li class="list-group-item">
                            <strong>{{ $log->user->name }}</strong> &mdash; {{ $log->description }}
                            <span class="text-muted small float-end">{{ $log->created_at->format('d M Y, H:i') }}</span>
                        </li>
                    @endforeach
                </ul>
            @endcan

            <h6 class="mt-4">Photos</h6>
            <div class="row g-2">
                @forelse($job->photos as $photo)
                    <div class="col-6 col-md-3">
                        <img src="{{ asset('storage/' . $photo->path) }}" class="img-fluid rounded" alt="Job completion">
                        @if($photo->location_captured)
                            <a href="{{ $photo->map_link }}" target="_blank" class="small d-block">{{ $photo->address ?? 'View location' }}</a>
                        @else
                            <span class="small text-danger d-block">Location not captured</span>
                        @endif
                        @if($photo->location_flagged)
                            <x-ui.status-badge :status="'Outside expected radius (' . number_format($photo->distance_from_machine_meters) . 'm)'" variant="danger" icon="ri-map-pin-line" />
                        @endif
                    </div>
                @empty
                    <div class="col-12"><x-ui.empty-state icon="ri-image-line" message="No photos uploaded yet." /></div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="modal fade" id="holdJobModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('jobs.hold', $job->id) }}" method="POST" class="job-action-form">
                    @csrf
                    <div class="modal-header"><h5 class="modal-title">Put Job On Hold</h5></div>
                    <div class="modal-body">
                        <label class="form-label" for="on-hold-reason">Reason <span class="text-danger">*</span></label>
                        <textarea id="on-hold-reason" name="on_hold_reason" class="form-control" required></textarea>
                    </div>
                    <div class="modal-footer">
                        <x-ui.button variant="secondary" type="button" data-bs-dismiss="modal">Cancel</x-ui.button>
                        <x-ui.button variant="warning" type="submit" icon="ri-pause-circle-line" class="btn-shopfloor">Confirm Hold</x-ui.button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('form.job-action-form').forEach(function (form) {
    form.addEventListener('submit', function (event) {
        var btn = form.querySelector('button[type="submit"]');
        if (!btn || btn.disabled) {
            return;
        }
        if (form.hasAttribute('onsubmit') && event.defaultPrevented) {
            return;
        }
        btn.disabled = true;
        btn.dataset.originalHtml = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving…';
    });
});

if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(function (position) {
        document.getElementById('photo-lat').value = position.coords.latitude;
        document.getElementById('photo-lng').value = position.coords.longitude;
        var status = document.getElementById('location-status');
        if (status) { status.textContent = 'Location captured ✓'; status.classList.replace('text-muted', 'text-success'); }
    }, function () {
        var status = document.getElementById('location-status');
        if (status) { status.textContent = 'Location not available ⚠'; status.classList.replace('text-muted', 'text-danger'); }
    });
}
</script>
