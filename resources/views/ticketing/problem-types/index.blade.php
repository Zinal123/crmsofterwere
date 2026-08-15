@extends('layouts.master')
@section('title')
Problem Types
@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1')
Problem Types
@endslot
@slot('title')
Problem Types
@endslot
@endcomponent

<div class="row">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Add Problem Type</h5></div>
            <div class="card-body">
                <form action="{{ route('admin.ticket-problem-types.store') }}" method="POST">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label" for="problem-type-category">Category <span class="text-danger">*</span></label>
                        <select id="problem-type-category" class="form-select" name="category" required>
                            @foreach(\App\Models\TicketProblemType::CATEGORIES as $category)
                            <option value="{{ $category }}">{{ ucwords(str_replace('_', ' ', $category)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" for="problem-type-name">Name <span class="text-danger">*</span></label>
                        <input id="problem-type-name" type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" for="problem-type-description">Description</label>
                        <textarea id="problem-type-description" class="form-control" name="description" rows="2"></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" for="problem-type-priority">Default Priority <span class="text-danger">*</span></label>
                        <select id="problem-type-priority" class="form-select" name="default_priority" required>
                            @foreach(\App\Models\TicketProblemType::PRIORITIES as $priority)
                            <option value="{{ $priority }}">{{ ucfirst($priority) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" for="problem-type-hours">Estimated Resolution Hours</label>
                        <input id="problem-type-hours" type="number" min="0" max="8760" class="form-control" name="estimated_resolution_hours">
                    </div>
                    <div class="mb-2">
                        <label class="form-label" for="problem-type-checklist-template">Checklist Template</label>
                        <select id="problem-type-checklist-template" class="form-select" name="checklist_template_id">
                            <option value="">None</option>
                            @foreach($templates as $template)
                            <option value="{{ $template->id }}">{{ $template->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <x-ui.button type="submit" variant="success" icon="ri-add-line">Add Problem Type</x-ui.button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <x-ui.data-table-card title="Problem Types">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr><th>Category</th><th>Name</th><th>Priority</th><th>Est. Hours</th><th>Checklist Template</th><th>Status</th><th></th></tr>
                    </thead>
                    <tbody>
                        @forelse($problemTypes as $problemType)
                        <tr>
                            <td>{{ ucwords(str_replace('_', ' ', $problemType->category)) }}</td>
                            <td>{{ $problemType->name }}</td>
                            <td>
                                <x-ui.priority-badge :priority="$problemType->default_priority" />
                            </td>
                            <td>{{ $problemType->estimated_resolution_hours ?? '—' }}</td>
                            <td>{{ $problemType->checklistTemplate->name ?? '—' }}</td>
                            <td>
                                <x-ui.status-badge
                                    :status="$problemType->is_active ? 'Active' : 'Inactive'"
                                    :variant="$problemType->is_active ? 'success' : 'danger'"
                                    :icon="$problemType->is_active ? 'ri-checkbox-circle-line' : 'ri-close-circle-line'"
                                />
                            </td>
                            <td>
                                <form action="{{ route('admin.ticket-problem-types.toggle', $problemType->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-soft-secondary btn-sm" data-bs-toggle="tooltip" title="{{ $problemType->is_active ? 'Disable' : 'Enable' }}" aria-label="{{ $problemType->is_active ? 'Disable' : 'Enable' }}">
                                        <i class="{{ $problemType->is_active ? 'ri-forbid-line' : 'ri-toggle-line' }} align-bottom"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7"><x-ui.empty-state icon="ri-error-warning-line" message="No problem types yet." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.data-table-card>
    </div>
</div>
@endsection
