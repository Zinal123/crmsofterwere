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
                        <label class="form-label" for="machine-invoice">Invoice ID (optional)</label>
                        <input id="machine-invoice" type="number" class="form-control" name="invoice_id">
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
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr><th>Client</th><th>Product</th><th>Serial Number</th><th>Installed</th></tr>
                    </thead>
                    <tbody>
                        @forelse($clientMachines as $machine)
                        <tr>
                            <td>{{ $machine->clientAccount->name ?? '-' }}</td>
                            <td>{{ $machine->product->name ?? '-' }}</td>
                            <td>{{ $machine->serial_number }}</td>
                            <td>{{ $machine->installed_at?->format('d M Y') ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4"><x-ui.empty-state icon="ri-tools-line" message="No machines registered yet." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.data-table-card>
    </div>
</div>
@endsection
