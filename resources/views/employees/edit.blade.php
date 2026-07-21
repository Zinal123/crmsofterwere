@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Edit Employee — {{ $employee->name }}</h5>
            <form action="{{ route('employees.update', $employee->id) }}" method="POST">
                @csrf
                @method('PUT')
                @include('employees._form')
                <x-ui.button variant="success" type="submit" icon="ri-save-line" ariaLabel="Save employee">Save</x-ui.button>
            </form>

            @if($employee->is_active)
                <form action="{{ route('employees.deactivate', $employee->id) }}" method="POST" class="mt-2 d-inline">
                    @csrf
                    <x-ui.button variant="danger" type="submit" icon="ri-user-unfollow-line" ariaLabel="Deactivate employee">Deactivate</x-ui.button>
                </form>
            @endif
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-body">
            <h5 class="card-title">ID Documents</h5>
            <div class="row g-2 mb-3">
                @forelse($employee->documents as $document)
                    <div class="col-md-3">
                        <div class="border rounded p-2">
                            <span class="badge bg-info-subtle text-info text-uppercase">{{ str_replace('_', ' ', $document->document_type) }}</span>
                            <p class="small mb-0">{{ $document->document_number }}</p>
                        </div>
                    </div>
                @empty
                    <div class="col-12"><x-ui.empty-state icon="ri-file-list-3-line" message="No documents uploaded yet." /></div>
                @endforelse
            </div>
            <form action="{{ route('employees.documents.store', $employee->id) }}" method="POST" enctype="multipart/form-data" class="d-flex gap-2 flex-wrap align-items-end">
                @csrf
                <div>
                    <label class="form-label" for="doc-type">Document Type</label>
                    <select id="doc-type" name="document_type" class="form-select" required>
                        <option value="aadhar">Aadhar</option>
                        <option value="pan">PAN</option>
                        <option value="driving_license">Driving License</option>
                        <option value="voter_id">Voter ID</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div>
                    <label class="form-label" for="doc-number">Document Number (optional)</label>
                    <input id="doc-number" type="text" name="document_number" class="form-control">
                </div>
                <div>
                    <label class="form-label" for="doc-file">File (image or PDF)</label>
                    <input id="doc-file" type="file" name="document" accept="image/*,application/pdf" class="form-control" required>
                </div>
                <x-ui.button variant="primary" type="submit" icon="ri-upload-line" ariaLabel="Upload document">Upload</x-ui.button>
            </form>
        </div>
    </div>

    @can('employees.view-audit')
    <div class="card mt-3">
        <div class="card-body">
            <h5 class="card-title">Audit Trail</h5>
            <x-ui.audit-trail :logs="app(\App\Services\Auditing\AuditLogService::class)->forRecord('employee', $employee->id)" />
        </div>
    </div>
    @endcan
</div>
@endsection
