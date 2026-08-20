@extends('layouts.master')
@section('title')
Client Accounts
@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1')
Client Accounts
@endslot
@slot('title')
Client Accounts
@endslot
@endcomponent

<div class="row">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Create Client Account</h5></div>
            <div class="card-body">
                <form action="{{ route('admin.client-accounts.store') }}" method="POST">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label" for="create-client-name">Name <span class="text-danger">*</span></label>
                        <input id="create-client-name" type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" for="create-client-email">Email <span class="text-danger">*</span></label>
                        <input id="create-client-email" type="email" class="form-control" name="email" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" for="create-client-phone">Phone</label>
                        <input id="create-client-phone" type="text" class="form-control" name="phone">
                    </div>
                    <div class="mb-2">
                        <label class="form-label" for="create-client-password">Temporary Password <span class="text-danger">*</span></label>
                        <input id="create-client-password" type="text" class="form-control" name="password" minlength="8" required>
                        <div class="form-text">Share this with the client directly. They can log in at the client portal.</div>
                    </div>
                    <x-ui.button type="submit" variant="success" icon="ri-add-line">Create Client Account</x-ui.button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <x-ui.data-table-card title="Client Accounts">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr><th>Name</th><th>Email</th><th>Phone</th><th>Status</th><th>Machines</th><th></th></tr>
                    </thead>
                    <tbody>
                        @forelse($clientAccounts as $account)
                        <tr id="client-{{ $account->id }}">
                            <td>{{ $account->name }}</td>
                            <td>{{ $account->email }}</td>
                            <td>{{ $account->phone ?: '-' }}</td>
                            <td>
                                <x-ui.status-badge
                                    :status="$account->is_active ? 'Active' : 'Inactive'"
                                    :variant="$account->is_active ? 'success' : 'danger'"
                                    :icon="$account->is_active ? 'ri-checkbox-circle-line' : 'ri-close-circle-line'"
                                />
                            </td>
                            <td>{{ $account->machines()->count() }}</td>
                            <td>
                                <div class="d-flex gap-2 flex-wrap">
                                    <button type="button" class="btn btn-soft-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editClientAccount-{{ $account->id }}" title="Edit" aria-label="Edit">
                                        <i class="ri-edit-line align-bottom"></i>
                                    </button>
                                    @can('client-machines.view-audit')
                                    <button type="button" class="btn btn-soft-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#auditTrailModal-client_account" data-audit-id="{{ $account->id }}" title="History" aria-label="History">
                                        <i class="ri-history-line align-bottom"></i>
                                    </button>
                                    @endcan
                                    <form action="{{ route('admin.client-accounts.toggle', $account->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-soft-{{ $account->is_active ? 'warning' : 'success' }} btn-sm" title="{{ $account->is_active ? 'Deactivate' : 'Activate' }}" aria-label="{{ $account->is_active ? 'Deactivate' : 'Activate' }}">
                                            <i class="{{ $account->is_active ? 'ri-forbid-line' : 'ri-checkbox-circle-line' }} align-bottom"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6"><x-ui.empty-state icon="ri-user-line" message="No client accounts yet." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.data-table-card>
    </div>
</div>

@foreach($clientAccounts as $account)
<div class="modal fade" id="editClientAccount-{{ $account->id }}" tabindex="-1" aria-labelledby="editClientAccount-{{ $account->id }}-label" aria-modal="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editClientAccount-{{ $account->id }}-label">Edit Client Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('admin.client-accounts.update', $account->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-2">
                        <label class="form-label" for="edit-client-name-{{ $account->id }}">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit-client-name-{{ $account->id }}" name="name" value="{{ $account->name }}" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" for="edit-client-email-{{ $account->id }}">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="edit-client-email-{{ $account->id }}" name="email" value="{{ $account->email }}" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" for="edit-client-phone-{{ $account->id }}">Phone</label>
                        <input type="text" class="form-control" id="edit-client-phone-{{ $account->id }}" name="phone" value="{{ $account->phone }}">
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

@can('client-machines.view-audit')
    <x-ui.audit-trail-modal type="client_account" />
@endcan
@endsection
