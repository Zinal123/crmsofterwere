@extends('layouts.master')
@section('title')
Client Machines
@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1')
Client Machines
@endslot
@slot('title')
Client Machines
@endslot
@endcomponent

<div class="row">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Register a Machine</h5></div>
            <div class="card-body">
                <form action="{{ route('admin.client-machines.store') }}" method="POST">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label" for="machine-client">Client <span class="text-danger">*</span></label>
                        <select id="machine-client" class="form-select" name="client_account_id" required>
                            <option value="">-- Select client --</option>
                            @foreach($clientAccounts as $account)
                                <option value="{{ $account->id }}">{{ $account->name }} ({{ $account->email }})</option>
                            @endforeach
                        </select>
                        @if($clientAccounts->isEmpty())
                            <div class="form-text">No client accounts yet - <a href="{{ route('admin.client-accounts.index') }}">create one first</a>.</div>
                        @endif
                    </div>
                    <div class="mb-2">
                        <label class="form-label" for="machine-product">Product / Model <span class="text-danger">*</span></label>
                        <select id="machine-product" class="form-select" name="product_id" required>
                            <option value="">-- Select product --</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" for="machine-serial">Serial Number <span class="text-danger">*</span></label>
                        <input id="machine-serial" type="text" class="form-control" name="serial_number" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" for="machine-invoice">Invoice (optional)</label>
                        <select id="machine-invoice" class="form-select" name="invoice_id">
                            <option value="">-- Not linked to an invoice --</option>
                            @foreach($invoices as $invoice)
                                <option value="{{ $invoice->id }}">#{{ $invoice->invoice_id ?? $invoice->id }} - {{ $invoice->customer->name ?? 'Unknown customer' }} - {{ \App\Support\IndianNumber::format($invoice->amount) }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">Links this machine back to the sale, if known.</div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" for="machine-installed">Installation Date</label>
                        <input id="machine-installed" type="date" class="form-control" name="installed_at">
                    </div>
                    <x-ui.button type="submit" variant="success" icon="ri-add-line">Register Machine</x-ui.button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <x-ui.data-table-card title="Client Machines">
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle">
                    <thead>
                        <tr><th>Client</th><th>Product</th><th>Serial Number</th><th>Installed</th><th></th></tr>
                    </thead>
                    <tbody>
                        @forelse($clientMachines as $machine)
                        <tr id="client-machine-{{ $machine->id }}">
                            <td>{{ $machine->clientAccount->name ?? '-' }}</td>
                            <td>{{ $machine->product->name ?? '-' }}</td>
                            <td>{{ $machine->serial_number }}</td>
                            <td>{{ $machine->installed_at?->format('d M Y') ?? '-' }}</td>
                            <td>
                                <div class="d-flex gap-2 flex-wrap">
                                    <button type="button" class="btn btn-soft-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editClientMachine-{{ $machine->id }}" title="Edit" aria-label="Edit">
                                        <i class="ri-edit-line align-bottom"></i>
                                    </button>
                                    @can('client-machines.view-audit')
                                    <button type="button" class="btn btn-soft-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#auditTrailModal-client_machine" data-audit-id="{{ $machine->id }}" title="History" aria-label="History">
                                        <i class="ri-history-line align-bottom"></i>
                                    </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5"><x-ui.empty-state icon="ri-tools-line" message="No machines registered yet." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.data-table-card>
    </div>
</div>

@foreach($clientMachines as $machine)
<div class="modal fade" id="editClientMachine-{{ $machine->id }}" tabindex="-1" aria-labelledby="editClientMachine-{{ $machine->id }}-label" aria-modal="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editClientMachine-{{ $machine->id }}-label">Edit Machine</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('admin.client-machines.update', $machine->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-2">
                        <label class="form-label" for="edit-machine-client-{{ $machine->id }}">Client <span class="text-danger">*</span></label>
                        <select id="edit-machine-client-{{ $machine->id }}" class="form-select" name="client_account_id" required>
                            @foreach($clientAccounts as $account)
                                <option value="{{ $account->id }}" @selected($machine->client_account_id === $account->id)>{{ $account->name }} ({{ $account->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" for="edit-machine-product-{{ $machine->id }}">Product / Model <span class="text-danger">*</span></label>
                        <select id="edit-machine-product-{{ $machine->id }}" class="form-select" name="product_id" required>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" @selected($machine->product_id === $product->id)>{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" for="edit-machine-serial-{{ $machine->id }}">Serial Number <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit-machine-serial-{{ $machine->id }}" name="serial_number" value="{{ $machine->serial_number }}" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" for="edit-machine-invoice-{{ $machine->id }}">Invoice (optional)</label>
                        <select id="edit-machine-invoice-{{ $machine->id }}" class="form-select" name="invoice_id">
                            <option value="">-- Not linked to an invoice --</option>
                            @foreach($invoices as $invoice)
                                <option value="{{ $invoice->id }}" @selected($machine->invoice_id === $invoice->id)>#{{ $invoice->invoice_id ?? $invoice->id }} - {{ $invoice->customer->name ?? 'Unknown customer' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" for="edit-machine-installed-{{ $machine->id }}">Installation Date</label>
                        <input type="date" class="form-control" id="edit-machine-installed-{{ $machine->id }}" name="installed_at" value="{{ $machine->installed_at?->format('Y-m-d') }}">
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
    <x-ui.audit-trail-modal type="client_machine" />
@endcan
@endsection
