@extends('layouts.master')
@section('title')
Vendors
@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1')
Vendors
@endslot
@slot('title')
Vendors
@endslot
@endcomponent

<div class="row">
    @can('vendors.manage')
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Add Vendor</h5></div>
            <div class="card-body">
                <form action="{{ route('admin.vendors.store') }}" method="POST">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label" for="vendor-name">Vendor Name <span class="text-danger">*</span></label>
                        <input id="vendor-name" type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" for="vendor-category">Category <span class="text-danger">*</span></label>
                        <select id="vendor-category" class="form-select" name="category" required>
                            @foreach(\App\Models\Vendor::CATEGORIES as $category)
                            <option value="{{ $category }}">{{ $category }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" for="vendor-gstin">GSTIN</label>
                        <input id="vendor-gstin" type="text" class="form-control" name="gstin">
                    </div>
                    <div class="mb-2">
                        <label class="form-label" for="vendor-contact-name">Contact Person</label>
                        <input id="vendor-contact-name" type="text" class="form-control" name="contact_name">
                    </div>
                    <div class="mb-2">
                        <label class="form-label" for="vendor-phone">Phone</label>
                        <input id="vendor-phone" type="text" class="form-control" name="phone">
                    </div>
                    <div class="mb-2">
                        <label class="form-label" for="vendor-email">Email</label>
                        <input id="vendor-email" type="email" class="form-control" name="email">
                    </div>
                    <div class="mb-2">
                        <label class="form-label" for="vendor-state">State</label>
                        <select id="vendor-state" class="form-select" name="state">
                            <option value="">-- Select State --</option>
                            @foreach(\App\Support\IndianStates::LIST as $state)
                            <option value="{{ $state }}">{{ $state }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" for="vendor-country">Country</label>
                        <input id="vendor-country" type="text" class="form-control" name="country" value="India">
                    </div>
                    <div class="mb-2">
                        <label class="form-label" for="vendor-notes">Notes</label>
                        <textarea id="vendor-notes" class="form-control" name="notes" rows="2"></textarea>
                    </div>
                    <x-ui.button type="submit" variant="success" icon="ri-add-line">Add Vendor</x-ui.button>
                </form>
            </div>
        </div>
    </div>
    @endcan

    <div class="{{ auth()->user()->can('vendors.manage') ? 'col-lg-8' : 'col-lg-12' }}">
        <x-ui.data-table-card title="Vendors">
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Category</th>
                            <th>GSTIN</th>
                            <th>Contact</th>
                            <th>Phone</th>
                            <th>State</th>
                            @canany(['vendors.manage', 'vendor-payments.view'])
                            <th></th>
                            @endcanany
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vendors as $vendor)
                        <tr>
                            <td>{{ $vendor->name }}</td>
                            <td>{{ $vendor->category }}</td>
                            <td>{{ $vendor->gstin ?? '-' }}</td>
                            <td>{{ $vendor->contact_name ?? '-' }}</td>
                            <td>{{ $vendor->phone ?? '-' }}</td>
                            <td>{{ $vendor->state ?? '-' }}</td>
                            @canany(['vendors.manage', 'vendor-payments.view'])
                            <td>
                                @can('vendor-payments.view')
                                <a href="{{ route('admin.vendors.show', $vendor->id) }}" class="btn btn-soft-secondary btn-sm" title="Payable Ledger" aria-label="Payable Ledger">
                                    <i class="ri-wallet-3-line align-bottom"></i>
                                </a>
                                @endcan
                                @can('vendors.manage')
                                <button type="button" class="btn btn-soft-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editVendor-{{ $vendor->id }}" title="Edit" aria-label="Edit">
                                    <i class="ri-edit-line align-bottom"></i>
                                </button>
                                <form action="{{ route('admin.vendors.destroy', $vendor->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-soft-danger btn-sm" data-confirm-delete title="Delete" aria-label="Delete">
                                        <i class="ri-delete-bin-fill align-bottom"></i>
                                    </button>
                                </form>
                                @endcan
                            </td>
                            @endcanany
                        </tr>
                        @empty
                        <tr><td colspan="7"><x-ui.empty-state icon="ri-truck-line" message="No vendors yet." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.data-table-card>
    </div>
</div>

@can('vendors.manage')
@foreach($vendors as $vendor)
<div class="modal fade" id="editVendor-{{ $vendor->id }}" tabindex="-1" aria-labelledby="editVendor-{{ $vendor->id }}-label" aria-modal="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editVendor-{{ $vendor->id }}-label">Edit Vendor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('admin.vendors.update', $vendor->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label class="form-label" for="edit-vendor-name-{{ $vendor->id }}">Vendor Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit-vendor-name-{{ $vendor->id }}" name="name" value="{{ $vendor->name }}" required>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label" for="edit-vendor-category-{{ $vendor->id }}">Category <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit-vendor-category-{{ $vendor->id }}" name="category" required>
                                @foreach(\App\Models\Vendor::CATEGORIES as $category)
                                <option value="{{ $category }}" @selected($vendor->category === $category)>{{ $category }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label" for="edit-vendor-gstin-{{ $vendor->id }}">GSTIN</label>
                            <input type="text" class="form-control" id="edit-vendor-gstin-{{ $vendor->id }}" name="gstin" value="{{ $vendor->gstin }}">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label" for="edit-vendor-contact-name-{{ $vendor->id }}">Contact Person</label>
                            <input type="text" class="form-control" id="edit-vendor-contact-name-{{ $vendor->id }}" name="contact_name" value="{{ $vendor->contact_name }}">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label" for="edit-vendor-phone-{{ $vendor->id }}">Phone</label>
                            <input type="text" class="form-control" id="edit-vendor-phone-{{ $vendor->id }}" name="phone" value="{{ $vendor->phone }}">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label" for="edit-vendor-email-{{ $vendor->id }}">Email</label>
                            <input type="email" class="form-control" id="edit-vendor-email-{{ $vendor->id }}" name="email" value="{{ $vendor->email }}">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label" for="edit-vendor-state-{{ $vendor->id }}">State</label>
                            <select class="form-select" id="edit-vendor-state-{{ $vendor->id }}" name="state">
                                <option value="">-- Select State --</option>
                                @foreach(\App\Support\IndianStates::LIST as $state)
                                <option value="{{ $state }}" @selected($vendor->state === $state)>{{ $state }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label" for="edit-vendor-country-{{ $vendor->id }}">Country</label>
                            <input type="text" class="form-control" id="edit-vendor-country-{{ $vendor->id }}" name="country" value="{{ $vendor->country }}">
                        </div>
                        <div class="col-md-12 mb-2">
                            <label class="form-label" for="edit-vendor-notes-{{ $vendor->id }}">Notes</label>
                            <textarea class="form-control" id="edit-vendor-notes-{{ $vendor->id }}" name="notes" rows="2">{{ $vendor->notes }}</textarea>
                        </div>
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
@endcan
<x-ui.confirm-modal recordType="vendor" />
@endsection
