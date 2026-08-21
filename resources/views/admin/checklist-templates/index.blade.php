@extends('layouts.master')
@section('title')
Checklist Templates
@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1')
Checklist Templates
@endslot
@slot('title')
Checklist Templates
@endslot
@endcomponent

<div class="row">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">New Template</h5></div>
            <div class="card-body">
                <form action="{{ route('admin.checklist-templates.store') }}" method="POST">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label" for="create-template-name">Name <span class="text-danger">*</span></label>
                        <input id="create-template-name" type="text" class="form-control" name="name" required>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="is_active" id="create-template-active" value="1" checked>
                        <label class="form-check-label" for="create-template-active">Active</label>
                    </div>
                    <x-ui.button type="submit" variant="success" icon="ri-add-line">Create Template</x-ui.button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        @forelse($templates as $template)
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0">
                    {{ $template->name }}
                    <x-ui.status-badge
                        :status="$template->is_active ? 'Active' : 'Inactive'"
                        :variant="$template->is_active ? 'success' : 'danger'"
                        :icon="$template->is_active ? 'ri-checkbox-circle-line' : 'ri-close-circle-line'"
                    />
                </h5>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-soft-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editTemplate-{{ $template->id }}" title="Edit" aria-label="Edit">
                        <i class="ri-edit-line align-bottom"></i>
                    </button>
                    <form action="{{ route('admin.checklist-templates.destroy', $template->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-soft-danger btn-sm" data-confirm-delete title="Delete" aria-label="Delete">
                            <i class="ri-delete-bin-fill align-bottom"></i>
                        </button>
                    </form>
                </div>
            </div>
            <div class="card-body">
                <ol class="list-group list-group-numbered mb-3">
                    @forelse($template->items as $item)
                    <li class="list-group-item d-flex align-items-center justify-content-between">
                        <span>{{ $item->description }}</span>
                        <form action="{{ route('admin.checklist-templates.items.destroy', $item->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-soft-danger btn-sm" data-confirm-delete title="Remove step" aria-label="Remove step">
                                <i class="ri-close-line align-bottom"></i>
                            </button>
                        </form>
                    </li>
                    @empty
                    <li class="list-group-item text-muted">No steps yet.</li>
                    @endforelse
                </ol>

                <form action="{{ route('admin.checklist-templates.items.store', $template->id) }}" method="POST" class="d-flex gap-2">
                    @csrf
                    <input type="text" class="form-control" name="description" placeholder="Add a step..." required aria-label="Step description">
                    <x-ui.button type="submit" variant="soft-success" icon="ri-add-line">Add</x-ui.button>
                </form>
            </div>
        </div>
        @empty
        <x-ui.data-table-card title="Checklist Templates">
            <x-ui.empty-state icon="ri-list-check-2" message="No templates yet." />
        </x-ui.data-table-card>
        @endforelse
    </div>
</div>

@foreach($templates as $template)
<div class="modal fade" id="editTemplate-{{ $template->id }}" tabindex="-1" aria-labelledby="editTemplate-{{ $template->id }}-label" aria-modal="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editTemplate-{{ $template->id }}-label">Edit Template</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('admin.checklist-templates.update', $template->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-2">
                        <label class="form-label" for="edit-template-name-{{ $template->id }}">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit-template-name-{{ $template->id }}" name="name" value="{{ $template->name }}" required>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="is_active" id="edit-template-active-{{ $template->id }}" value="1" @checked($template->is_active)>
                        <label class="form-check-label" for="edit-template-active-{{ $template->id }}">Active</label>
                    </div>
                    <div class="hstack gap-2 justify-content-end">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endforeach

<x-ui.confirm-modal recordType="record" />
@endsection
