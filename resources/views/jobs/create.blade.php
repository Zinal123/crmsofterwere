@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <x-ui.back-link :route="route('jobs.index')" label="Back to Jobs" />
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">{{ $canAssign ? 'Request or Assign a Job' : 'Request a Job' }}</h5>
            <form action="{{ route('jobs.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="job-title">Title <span class="text-danger">*</span></label>
                    <input id="job-title" type="text" name="title" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="job-description">Description</label>
                    <textarea id="job-description" name="description" class="form-control"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="job-machine">Machine (in-house)</label>
                    <div class="d-flex gap-2 align-items-start flex-wrap">
                        <select id="job-machine" name="machine_id" class="form-select" style="max-width: 320px;">
                            <option value="">-- Outside site instead --</option>
                            @foreach($machines as $machine)
                                <option value="{{ $machine->id }}">{{ $machine->name }}</option>
                            @endforeach
                        </select>
                        <x-ui.button variant="secondary" :soft="true" type="button" id="scan-machine-qr-btn" icon="ri-qr-scan-2-line">Scan QR</x-ui.button>
                    </div>
                    <div id="machine-scan-area" class="mt-2" style="display: none;">
                        <video id="machine-scan-video" playsinline muted class="rounded" style="width: 100%; max-width: 320px; max-height: 240px; background: #000;"></video>
                        <canvas id="machine-scan-canvas" style="display: none;"></canvas>
                        <p id="machine-scan-status" class="small text-muted mb-1">Point the camera at the machine's QR tag.</p>
                        <x-ui.button variant="secondary" type="button" id="cancel-machine-scan-btn" size="sm">Cancel Scan</x-ui.button>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="job-site">Outside Site Name</label>
                    <input id="job-site" type="text" name="site_name" class="form-control" placeholder="Only if not an in-house machine">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="job-priority">Priority</label>
                    <select id="job-priority" name="priority" class="form-select">
                        <option value="low">Low</option>
                        <option value="medium" selected>Medium</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="job-due-date">Due Date (optional)</label>
                    <input id="job-due-date" type="date" name="due_date" class="form-control">
                </div>
                @if($canAssign)
                    <div class="mb-3">
                        <label class="form-label" for="job-assign">Assign to Worker (skips approval)</label>
                        <select id="job-assign" name="assigned_to" class="form-select">
                            <option value="">-- Leave blank to submit as your own request --</option>
                            @foreach($workers as $worker)
                                <option value="{{ $worker->id }}">{{ $worker->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <x-ui.button variant="success" type="submit" icon="ri-send-plane-line" ariaLabel="Submit job">Submit</x-ui.button>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="{{ URL::asset('build/libs/jsqr/jsQR.js') }}"></script>
<script>
(function () {
    var scanBtn = document.getElementById('scan-machine-qr-btn');
    var cancelBtn = document.getElementById('cancel-machine-scan-btn');
    var scanArea = document.getElementById('machine-scan-area');
    var video = document.getElementById('machine-scan-video');
    var canvas = document.getElementById('machine-scan-canvas');
    var status = document.getElementById('machine-scan-status');
    var machineSelect = document.getElementById('job-machine');
    var ctx = canvas.getContext('2d', { willReadFrequently: true });
    var stream = null;
    var scanning = false;

    // Must match App\Support\MachineQrCode::token() - see that class for
    // why the format isn't shared between PHP and this vendored JS decoder.
    var TOKEN_PATTERN = /^OMT-MACHINE-(\d+)$/;

    if (!scanBtn || !(navigator.mediaDevices && navigator.mediaDevices.getUserMedia) || typeof jsQR === 'undefined') {
        if (scanBtn) {
            scanBtn.style.display = 'none';
        }
        return;
    }

    function stopScan() {
        scanning = false;
        if (stream) {
            stream.getTracks().forEach(function (track) { track.stop(); });
            stream = null;
        }
        scanArea.style.display = 'none';
    }

    function scanFrame() {
        if (!scanning) {
            return;
        }
        if (video.readyState === video.HAVE_ENOUGH_DATA) {
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
            var imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
            var code = jsQR(imageData.data, imageData.width, imageData.height);

            if (code) {
                var match = TOKEN_PATTERN.exec(code.data);
                if (!match) {
                    status.textContent = 'That QR code isn\'t a recognized machine tag - try another.';
                    status.classList.replace('text-muted', 'text-danger');
                } else {
                    var option = machineSelect.querySelector('option[value="' + match[1] + '"]');
                    if (!option) {
                        status.textContent = 'Machine tag scanned, but that machine isn\'t in your list.';
                        status.classList.replace('text-muted', 'text-danger');
                    } else {
                        machineSelect.value = match[1];
                        stopScan();
                        return;
                    }
                }
            }
        }
        requestAnimationFrame(scanFrame);
    }

    scanBtn.addEventListener('click', function () {
        status.textContent = 'Point the camera at the machine\'s QR tag.';
        status.classList.replace('text-danger', 'text-muted');
        scanArea.style.display = 'block';

        navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } }).then(function (mediaStream) {
            stream = mediaStream;
            video.srcObject = stream;
            video.play();
            scanning = true;
            requestAnimationFrame(scanFrame);
        }).catch(function () {
            status.textContent = 'Camera access denied or unavailable - use the dropdown instead.';
            status.classList.replace('text-muted', 'text-danger');
        });
    });

    cancelBtn.addEventListener('click', stopScan);
})();
</script>
@endsection
