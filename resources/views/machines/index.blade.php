@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <x-ui.back-link :route="route('root')" label="Back to Dashboard" />
    <x-ui.data-table-card title="Machines">
        <table class="table table-bordered align-middle" id="machinesTable">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($machines as $machine)
                    <tr>
                        <td>{{ $machine->name }}</td>
                        <td>
                            <x-ui.status-badge
                                :status="$machine->is_active ? 'Active' : 'Inactive'"
                                :variant="$machine->is_active ? 'success' : 'secondary'"
                                icon="{{ $machine->is_active ? 'ri-checkbox-circle-line' : 'ri-close-circle-line' }}" />
                        </td>
                        <td>
                            <div class="d-flex gap-2 flex-wrap">
                                <form action="{{ route('machines.toggle', $machine->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <x-ui.button variant="secondary" :soft="true" size="sm" type="submit" :icon="$machine->is_active ? 'ri-forbid-line' : 'ri-toggle-line'" :ariaLabel="$machine->is_active ? 'Disable' : 'Enable'" data-bs-toggle="tooltip" :title="$machine->is_active ? 'Disable' : 'Enable'" />
                                </form>
                                <x-ui.button variant="secondary" :soft="true" size="sm" type="button" icon="ri-qr-code-line" class="show-machine-qr" data-machine-id="{{ $machine->id }}" data-machine-name="{{ $machine->name }}" data-machine-token="{{ \App\Support\MachineQrCode::token($machine->id) }}" ariaLabel="QR Code" title="QR Code" />
                                @can('machines.view-audit')
                                <x-ui.button variant="secondary" :soft="true" size="sm" type="button" icon="ri-history-line" data-bs-toggle="modal" data-bs-target="#auditTrailModal-machine" data-audit-id="{{ $machine->id }}" ariaLabel="History" title="History" />
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3"><x-ui.empty-state icon="ri-tools-line" message="No machines added yet." /></td></tr>
                @endforelse
            </tbody>
        </table>
    </x-ui.data-table-card>

    <div class="card mt-3">
        <div class="card-body">
            <h5 class="card-title">Add Machine</h5>
            <form action="{{ route('machines.store') }}" method="POST" class="d-flex gap-2 flex-wrap">
                @csrf
                <label for="machine-name" class="visually-hidden">Machine name</label>
                <input id="machine-name" type="text" name="name" class="form-control" style="max-width: 260px;" placeholder="Machine name" required>
                <label for="machine-latitude" class="visually-hidden">Latitude</label>
                <input id="machine-latitude" type="number" step="0.0000001" name="latitude" class="form-control @error('latitude') is-invalid @enderror" style="max-width: 160px;" placeholder="Latitude (optional)">
                <label for="machine-longitude" class="visually-hidden">Longitude</label>
                <input id="machine-longitude" type="number" step="0.0000001" name="longitude" class="form-control @error('longitude') is-invalid @enderror" style="max-width: 160px;" placeholder="Longitude (optional)">
                <x-ui.button variant="success" type="submit" icon="ri-add-line" ariaLabel="Add machine">Add</x-ui.button>
                @error('latitude')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                @error('longitude')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </form>
            <p class="text-muted small mt-2 mb-0">Coordinates are optional today, but will be required once GPS geofencing on job photos ships — set them where known.</p>
        </div>
    </div>
</div>
@can('machines.view-audit')
    <x-ui.audit-trail-modal type="machine" />
@endcan

<div class="modal fade" id="machineQrModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="machine-qr-title">Machine QR Code</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div id="machine-qr-code" class="d-inline-block"></div>
                <p class="text-muted small mt-2 mb-0">Print and affix to the machine. Scanning it during job creation selects this machine automatically instead of the dropdown.</p>
            </div>
            <div class="modal-footer">
                <x-ui.button variant="secondary" type="button" data-bs-dismiss="modal">Close</x-ui.button>
                <x-ui.button variant="primary" type="button" id="print-machine-qr" icon="ri-printer-line">Print</x-ui.button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="{{ URL::asset('build/libs/qrcodejs/qrcode.js') }}"></script>
<style media="print">
    body * { visibility: hidden; }
    #machine-qr-code, #machine-qr-code * { visibility: visible; }
    #machine-qr-code { position: absolute; top: 0; left: 0; }
</style>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var qrContainer = document.getElementById('machine-qr-code');
    var qrTitle = document.getElementById('machine-qr-title');
    var currentQr = null;

    document.querySelectorAll('.show-machine-qr').forEach(function (button) {
        button.addEventListener('click', function () {
            qrTitle.textContent = button.dataset.machineName + ' — QR Code';
            qrContainer.innerHTML = '';
            currentQr = new QRCode(qrContainer, {
                text: button.dataset.machineToken,
                width: 220,
                height: 220,
            });
            new bootstrap.Modal(document.getElementById('machineQrModal')).show();
        });
    });

    document.getElementById('print-machine-qr').addEventListener('click', function () {
        window.print();
    });
});
</script>
@endsection
