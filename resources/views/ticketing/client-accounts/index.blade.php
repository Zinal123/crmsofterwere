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
                        <tr><th>Name</th><th>Email</th><th>Phone</th><th>Status</th><th>Machines</th></tr>
                    </thead>
                    <tbody>
                        @forelse($clientAccounts as $account)
                        <tr>
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
                        </tr>
                        @empty
                        <tr><td colspan="5"><x-ui.empty-state icon="ri-user-line" message="No client accounts yet." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.data-table-card>
    </div>
</div>
@endsection
