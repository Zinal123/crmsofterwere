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

                            @isset($checklistTemplates)
                                @if($checklistTemplates->isNotEmpty())
                                    <form action="{{ route('jobs.apply-template', $job->id) }}" method="POST" class="d-flex gap-2 mt-2">
                                        @csrf
                                        <label for="apply-template" class="visually-hidden">Apply traveler template</label>
                                        <select id="apply-template" name="checklist_template_id" class="form-select form-select-sm" required>
                                            <option value="" selected disabled>Apply traveler template…</option>
                                            @foreach($checklistTemplates as $template)
                                                <option value="{{ $template->id }}">{{ $template->name }}</option>
                                            @endforeach
                                        </select>
                                        <x-ui.button variant="secondary" size="sm" type="submit" icon="ri-file-list-3-line" ariaLabel="Apply traveler template">Apply</x-ui.button>
                                    </form>
                                @endif
                            @endisset
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

                            <div class="mb-2" role="radiogroup" aria-label="Photo stage">
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="stage-radio" id="stage-before" value="before" autocomplete="off">
                                    <label class="btn btn-outline-secondary btn-shopfloor" for="stage-before" style="min-height: 48px;">Before</label>

                                    <input type="radio" class="btn-check" name="stage-radio" id="stage-general" value="general" autocomplete="off" checked>
                                    <label class="btn btn-outline-secondary btn-shopfloor" for="stage-general" style="min-height: 48px;">General</label>

                                    <input type="radio" class="btn-check" name="stage-radio" id="stage-after" value="after" autocomplete="off">
                                    <label class="btn btn-outline-secondary btn-shopfloor" for="stage-after" style="min-height: 48px;">After</label>
                                </div>
                            </div>

                            <form id="photo-upload-form" class="job-action-form" action="{{ route('jobs.photos.store', $job->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="stage" id="photo-stage" value="general">

                                <div id="camera-capture">
                                    <video id="camera-video" autoplay playsinline muted class="w-100 rounded mb-2" style="max-height: 320px; background: #000; display: none;"></video>
                                    {{-- Canvas doubles as the annotation surface: after Capture, the frozen
                                         frame stays on canvas (not a static <img>) so the Worker can draw
                                         directly on it (arrows/notes) before it's flattened and uploaded. --}}
                                    <canvas id="camera-canvas" class="w-100 rounded mb-2" style="max-height: 320px; background: #000; display: none; touch-action: none;" aria-label="Captured proof photo, tap or drag to annotate"></canvas>

                                    <div class="d-flex gap-2 flex-wrap mb-2">
                                        <x-ui.button variant="primary" type="button" id="start-camera-btn" icon="ri-camera-line" class="btn-shopfloor">Open Camera</x-ui.button>
                                        <x-ui.button variant="success" type="button" id="capture-btn" icon="ri-checkbox-circle-line" class="btn-shopfloor" style="display: none;">Capture</x-ui.button>
                                        <x-ui.button variant="secondary" type="button" id="clear-annotation-btn" icon="ri-eraser-line" class="btn-shopfloor" style="display: none;">Clear Markup</x-ui.button>
                                        <x-ui.button variant="secondary" type="button" id="retake-btn" icon="ri-refresh-line" class="btn-shopfloor" style="display: none;">Retake</x-ui.button>
                                    </div>
                                    <p id="annotation-hint" class="small text-muted" style="display: none;">Draw on the photo above to mark or annotate anything - fingertip or stylus.</p>

                                    {{-- Camera capture draws to canvas and attaches the blob on submit (see script below) -
                                         this structurally blocks gallery selection, unlike a plain file-picker hint.
                                         Only shown as a fallback when getUserMedia isn't available at all. --}}
                                    <p id="camera-fallback-note" class="small text-muted" style="display: none;">Live camera capture isn't available on this device - falling back to your device's photo picker.</p>
                                    <label for="camera-fallback-input" class="visually-hidden">Photo</label>
                                    <input type="file" id="camera-fallback-input" name="photo" accept="image/*" capture="environment" class="form-control mb-2" style="display: none;">
                                </div>

                                <input type="hidden" name="latitude" id="photo-lat">
                                <input type="hidden" name="longitude" id="photo-lng">
                                <p id="location-status" class="small text-muted">Checking location…</p>
                                <x-ui.button variant="primary" type="submit" icon="ri-upload-line" ariaLabel="Upload photo" class="btn-shopfloor" id="photo-upload-submit">Upload</x-ui.button>
                            </form>

                            {{-- Populated by IndexedDB, not the server - photos captured with no
                                 signal live here until a real upload succeeds. See the script below;
                                 captured_at is still stamped server-side only at actual receipt. --}}
                            <div id="offline-queue-panel" style="display: none;" class="mt-3">
                                <x-ui.status-badge status="Pending Sync" variant="warning" icon="ri-wifi-off-line" />
                                <p class="small text-muted mb-2" id="offline-queue-count"></p>
                                <x-ui.button variant="warning" type="button" id="sync-now-btn" icon="ri-refresh-line" class="btn-shopfloor">Sync Now</x-ui.button>
                            </div>
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
                        @if($photo->stage !== 'general')
                            <x-ui.status-badge
                                :status="ucfirst($photo->stage)"
                                :variant="$photo->stage === 'before' ? 'warning' : 'success'"
                                :icon="$photo->stage === 'before' ? 'ri-history-line' : 'ri-checkbox-circle-line'" />
                        @endif
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

(function () {
    var uploadForm = document.getElementById('photo-upload-form');
    if (!uploadForm) {
        return;
    }

    var stageInput = document.getElementById('photo-stage');
    document.querySelectorAll('input[name="stage-radio"]').forEach(function (radio) {
        radio.addEventListener('change', function () {
            if (radio.checked) {
                stageInput.value = radio.value;
            }
        });
    });

    var video = document.getElementById('camera-video');
    var canvas = document.getElementById('camera-canvas');
    var ctx = canvas.getContext('2d');
    var startBtn = document.getElementById('start-camera-btn');
    var captureBtn = document.getElementById('capture-btn');
    var clearAnnotationBtn = document.getElementById('clear-annotation-btn');
    var retakeBtn = document.getElementById('retake-btn');
    var annotationHint = document.getElementById('annotation-hint');
    var fallbackNote = document.getElementById('camera-fallback-note');
    var fallbackInput = document.getElementById('camera-fallback-input');
    var stream = null;
    var hasCapturedPhoto = false;
    var originalFrame = null; // ImageData of the un-annotated capture, for "Clear Markup"
    var isDrawing = false;

    function stopStream() {
        if (stream) {
            stream.getTracks().forEach(function (track) { track.stop(); });
            stream = null;
        }
    }

    function fallBackToFilePicker() {
        stopStream();
        video.style.display = 'none';
        canvas.style.display = 'none';
        startBtn.style.display = 'none';
        captureBtn.style.display = 'none';
        clearAnnotationBtn.style.display = 'none';
        retakeBtn.style.display = 'none';
        annotationHint.style.display = 'none';
        fallbackNote.style.display = 'block';
        fallbackInput.style.display = 'block';
        fallbackInput.required = true;
    }

    function canvasPoint(event) {
        var rect = canvas.getBoundingClientRect();
        var point = event.touches ? event.touches[0] : event;
        return {
            x: (point.clientX - rect.left) * (canvas.width / rect.width),
            y: (point.clientY - rect.top) * (canvas.height / rect.height),
        };
    }

    function startDrawing(event) {
        if (!hasCapturedPhoto) {
            return;
        }
        isDrawing = true;
        var p = canvasPoint(event);
        ctx.beginPath();
        ctx.moveTo(p.x, p.y);
        event.preventDefault();
    }

    function draw(event) {
        if (!isDrawing) {
            return;
        }
        var p = canvasPoint(event);
        ctx.lineWidth = Math.max(4, canvas.width * 0.006);
        ctx.lineCap = 'round';
        ctx.strokeStyle = '#ff3b30';
        ctx.lineTo(p.x, p.y);
        ctx.stroke();
        event.preventDefault();
    }

    function stopDrawing() {
        isDrawing = false;
    }

    canvas.addEventListener('mousedown', startDrawing);
    canvas.addEventListener('mousemove', draw);
    window.addEventListener('mouseup', stopDrawing);
    canvas.addEventListener('touchstart', startDrawing, { passive: false });
    canvas.addEventListener('touchmove', draw, { passive: false });
    canvas.addEventListener('touchend', stopDrawing);

    var supportsCamera = !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia);

    if (!supportsCamera) {
        fallBackToFilePicker();
    } else {
        startBtn.addEventListener('click', function () {
            navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } }).then(function (mediaStream) {
                stream = mediaStream;
                video.srcObject = stream;
                video.style.display = 'block';
                startBtn.style.display = 'none';
                captureBtn.style.display = 'inline-flex';
            }).catch(fallBackToFilePicker);
        });

        captureBtn.addEventListener('click', function () {
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            ctx.drawImage(video, 0, 0);
            originalFrame = ctx.getImageData(0, 0, canvas.width, canvas.height);
            hasCapturedPhoto = true;

            video.style.display = 'none';
            canvas.style.display = 'block';
            captureBtn.style.display = 'none';
            clearAnnotationBtn.style.display = 'inline-flex';
            retakeBtn.style.display = 'inline-flex';
            annotationHint.style.display = 'block';
            stopStream();
        });

        clearAnnotationBtn.addEventListener('click', function () {
            if (originalFrame) {
                ctx.putImageData(originalFrame, 0, 0);
            }
        });

        retakeBtn.addEventListener('click', function () {
            hasCapturedPhoto = false;
            originalFrame = null;
            canvas.style.display = 'none';
            clearAnnotationBtn.style.display = 'none';
            retakeBtn.style.display = 'none';
            annotationHint.style.display = 'none';
            startBtn.style.display = 'inline-flex';
        });
    }

    // ---- Offline queue -------------------------------------------------
    // A photo captured with no signal is never lost: it's queued in
    // IndexedDB (blob + job/GPS/stage metadata + a client-side pending id
    // used only to show it in the UI, never trusted as captured_at) and
    // retried on reconnect. The server still stamps the authoritative
    // captured_at only when the upload actually lands - see
    // JobPhotoService::upload(), unchanged by any of this.
    var OFFLINE_DB_NAME = 'oracle-crm-offline-photos';
    var OFFLINE_STORE_NAME = 'pending_photos';
    var jobId = {{ $job->id }};
    // Replay data stored with each queued photo so it can upload standalone
    // (from the page OR from the service worker's Background Sync). Injected
    // server-side so it's always the signed-in worker's own identity/token.
    var OMT_USER_ID = @json((string) auth()->id());
    var OMT_CSRF = @json(csrf_token());
    var OMT_UPLOAD_URL = uploadForm ? uploadForm.action : '{{ route('jobs.photos.store', $job->id) }}';

    // Ask the service worker to drain the outbox when connectivity returns,
    // even if this tab is closed. Feature-detected; the online-event drain
    // below stays as the fallback (e.g. iOS Safari has no Background Sync).
    function requestBackgroundSync() {
        if ('serviceWorker' in navigator && navigator.serviceWorker.ready) {
            navigator.serviceWorker.ready.then(function (reg) {
                if ('sync' in reg) { reg.sync.register('omt-photo-outbox').catch(function () {}); }
            }).catch(function () {});
        }
    }

    // The page updates its queue panel when the SW reports a background drain.
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.addEventListener('message', function (e) {
            if (e.data && e.data.type === 'omt-outbox-drained') { renderOfflineQueuePanel(); }
        });
    }

    function openOfflineDb() {
        return new Promise(function (resolve, reject) {
            var request = indexedDB.open(OFFLINE_DB_NAME, 1);
            request.onupgradeneeded = function () {
                var db = request.result;
                if (!db.objectStoreNames.contains(OFFLINE_STORE_NAME)) {
                    db.createObjectStore(OFFLINE_STORE_NAME, { keyPath: 'id', autoIncrement: true });
                }
            };
            request.onsuccess = function () { resolve(request.result); };
            request.onerror = function () { reject(request.error); };
        });
    }

    function queuePhotoOffline(record) {
        return openOfflineDb().then(function (db) {
            return new Promise(function (resolve, reject) {
                var tx = db.transaction(OFFLINE_STORE_NAME, 'readwrite');
                tx.objectStore(OFFLINE_STORE_NAME).add(record);
                tx.oncomplete = function () { resolve(); };
                tx.onerror = function () { reject(tx.error); };
            });
        });
    }

    function getPendingPhotosForJob() {
        return openOfflineDb().then(function (db) {
            return new Promise(function (resolve, reject) {
                var request = db.transaction(OFFLINE_STORE_NAME, 'readonly').objectStore(OFFLINE_STORE_NAME).getAll();
                request.onsuccess = function () {
                    // Only this worker's photos for this job — on a shared tablet a
                    // record from a previous worker (different userId) is never shown
                    // or synced here. Legacy records with no userId are treated as own.
                    resolve(request.result.filter(function (record) {
                        return record.jobId === jobId
                            && (record.userId == null || String(record.userId) === String(OMT_USER_ID));
                    }));
                };
                request.onerror = function () { reject(request.error); };
            });
        });
    }

    function removePendingPhoto(id) {
        return openOfflineDb().then(function (db) {
            return new Promise(function (resolve, reject) {
                var tx = db.transaction(OFFLINE_STORE_NAME, 'readwrite');
                tx.objectStore(OFFLINE_STORE_NAME).delete(id);
                tx.oncomplete = function () { resolve(); };
                tx.onerror = function () { reject(tx.error); };
            });
        });
    }

    function uploadPhotoBlob(blob, meta) {
        var formData = new FormData();
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
        formData.append('photo', blob, 'proof-' + Date.now() + '.jpg');
        formData.append('latitude', meta.latitude);
        formData.append('longitude', meta.longitude);
        formData.append('stage', meta.stage);

        return fetch(uploadForm.action, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });
    }

    function renderOfflineQueuePanel() {
        var panel = document.getElementById('offline-queue-panel');
        var countText = document.getElementById('offline-queue-count');
        if (!panel) {
            return;
        }
        getPendingPhotosForJob().then(function (records) {
            if (records.length === 0) {
                panel.style.display = 'none';
                return;
            }
            panel.style.display = 'block';
            countText.textContent = records.length + ' photo(s) captured offline, waiting to upload.';
        }).catch(function () { /* IndexedDB unavailable - nothing queued to show */ });
    }

    function syncPendingPhotos() {
        return getPendingPhotosForJob().then(function (records) {
            var chain = Promise.resolve();
            records.forEach(function (record) {
                chain = chain.then(function () {
                    return uploadPhotoBlob(record.blob, record).then(function (response) {
                        if (response.status === 201) {
                            return removePendingPhoto(record.id);
                        }
                        // Still queued (e.g. job no longer in_progress) - leave it for the
                        // Worker to see and deal with via Sync Now, don't lose it silently.
                    }).catch(function () {
                        // Still offline - stop trying the rest for now, next trigger retries all.
                        return Promise.reject(new Error('offline'));
                    });
                });
            });
            return chain.catch(function () { /* stop the chain quietly */ }).then(function () {
                renderOfflineQueuePanel();
                if (records.length > 0) {
                    window.location.reload();
                }
            });
        });
    }

    var syncNowBtn = document.getElementById('sync-now-btn');
    if (syncNowBtn) {
        syncNowBtn.addEventListener('click', syncPendingPhotos);
    }
    window.addEventListener('online', syncPendingPhotos);
    renderOfflineQueuePanel();
    if (navigator.onLine) {
        syncPendingPhotos();
    }

    uploadForm.addEventListener('submit', function (event) {
        if (!supportsCamera) {
            return; // native file input submits normally
        }

        event.preventDefault();

        if (!hasCapturedPhoto) {
            alert('Please capture a photo first.');
            return;
        }

        canvas.toBlob(function (blob) {
            var meta = {
                jobId: jobId,
                latitude: document.getElementById('photo-lat').value,
                longitude: document.getElementById('photo-lng').value,
                stage: stageInput.value,
            };

            uploadPhotoBlob(blob, meta).then(function (response) {
                if (response.status === 201) {
                    window.location.reload();
                    return;
                }
                if (response.status === 422) {
                    return response.json().then(function (data) {
                        alert(data.message || 'Upload failed. Please try again.');
                        window.location.reload();
                    });
                }
                if (response.status === 401) {
                    window.location.href = '{{ route('login') }}';
                    return;
                }
                if (response.redirected) {
                    window.location.href = response.url;
                    return;
                }
                window.location.reload();
            }).catch(function () {
                // Network failure, not a validation error - queue instead of losing the photo.
                queuePhotoOffline({ jobId: meta.jobId, blob: blob, latitude: meta.latitude, longitude: meta.longitude, stage: meta.stage, userId: OMT_USER_ID, csrfToken: OMT_CSRF, url: OMT_UPLOAD_URL, queuedAt: Date.now() })
                    .then(function () {
                        requestBackgroundSync();
                        alert('No connection - photo saved on this device and will upload automatically once you\'re back online.');
                        renderOfflineQueuePanel();
                    })
                    .catch(function () {
                        alert('Upload failed and this device could not save it offline either. Please try again once you have a connection.');
                    });
            });
        }, 'image/jpeg', 0.9);
    });
})();
</script>
