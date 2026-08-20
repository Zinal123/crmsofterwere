@extends('layouts.master')
@section('title')
@lang('Product')
@endsection
@section('css')
<link href="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />

@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1')
Product
@endslot
@slot('title')
list view
@endslot
@endcomponent

<div class="row">
    <div class="col-lg-12">
        <x-ui.data-table-card title="Products">
            <div class="d-flex gap-2 flex-wrap mb-3">
                <x-ui.button variant="success" size="sm" icon="ri-add-line" data-bs-toggle="modal" data-bs-target="#exampleModalgrid">Create Product</x-ui.button>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered align-middle" id="products-table">
                    <thead class="text-muted">
                        <tr>
                            <th class="sort text-uppercase" data-sort="invoice_id">ID</th>
                            <th class="sort text-uppercase" data-sort="product">
                                Product</th>
                            <th class="sort text-uppercase" data-sort="make">Make</th>
                            <th class="sort text-uppercase" data-sort="rate">Rate</th>
                            <th class="sort text-uppercase" data-sort="quantity">Quantity</th>
                            <th class="sort text-uppercase" data-sort="vendor">Vendor</th>

                            <th class="sort text-uppercase" data-sort="action">Action</th>
                        </tr>
                    </thead>
                    <tbody class="list form-check-all">
                    @foreach ($product as $item)
                        <tr>
                        <td>{{$item->id}}</td>
                        <td>{{$item->name}}</td>
                        <td>{{$item->make}}</td>
                        <td>{{$item->rate}}</td>
                        <td>
                            @if($item->inventory)
                                {{ $item->inventory->quantity }}
                                @if($item->inventory->quantity < $item->inventory->effectiveLowStockThreshold())
                                    <x-ui.status-badge status="Low Stock" variant="danger" icon="ri-error-warning-line" />
                                @endif
                                @if($item->is_spare_part && isset($item->reserved_quantity))
                                    <div class="small text-muted">Reserved: {{ $item->reserved_quantity }} &middot; Available: {{ $item->available_quantity }}</div>
                                @endif
                            @else
                                <span class="badge bg-secondary-subtle text-secondary">Not tracked</span>
                            @endif
                        </td>
                        <td>
                            @if($item->inventory?->vendor_id)
                                <a href="{{ route('admin.vendors.show', $item->inventory->vendor_id) }}">{{ $item->inventory->vendor->name }}</a>
                            @else
                                {{ $item->inventory->vandername ?? '—' }}
                            @endif
                        </td>

                            <td>
                            <div class="d-flex gap-2 flex-wrap">
                                @can('products.update')
                                <button type="button" class="btn btn-soft-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editProduct-{{ $item->id }}" title="Edit" aria-label="Edit">
                                    <i class="ri-edit-line align-bottom"></i>
                                </button>
                                @endcan
                                @can('inventory.update')
                                    @if($item->inventory)
                                    <button type="button" class="btn btn-soft-primary btn-sm open-update-qty-modal" data-id="{{ $item->inventory->id }}" data-bs-toggle="tooltip" title="Update Qty" aria-label="Update Qty">
                                        <i class="ri-stack-line align-bottom"></i>
                                    </button>
                                    <form action="{{ route('inventory.set-low-stock-threshold', $item->inventory->id) }}" method="POST" class="d-inline-flex align-items-center gap-1">
                                        @csrf
                                        <label class="visually-hidden" for="threshold-{{ $item->inventory->id }}">Low-stock threshold</label>
                                        <input type="number" min="0" name="low_stock_threshold" id="threshold-{{ $item->inventory->id }}" class="form-control form-control-sm" style="width: 70px;" value="{{ $item->inventory->low_stock_threshold }}" placeholder="{{ \App\Models\Invetry::LOW_STOCK_THRESHOLD }}" title="Low-stock threshold (default {{ \App\Models\Invetry::LOW_STOCK_THRESHOLD }})">
                                        <button type="submit" class="btn btn-soft-secondary btn-sm" title="Save low-stock threshold" aria-label="Save low-stock threshold"><i class="ri-alarm-warning-line align-bottom"></i></button>
                                    </form>
                                    @endif
                                @endcan
                                @can('inventory.create')
                                    @if(!$item->inventory)
                                    <button type="button" class="btn btn-soft-success btn-sm open-add-stock-modal" data-product-id="{{ $item->id }}" data-product-name="{{ $item->name }}" data-bs-toggle="tooltip" title="Add Stock" aria-label="Add Stock">
                                        <i class="ri-add-box-line align-bottom"></i>
                                    </button>
                                    @endif
                                @endcan
                                @can('products.view-audit')
                                <button type="button" class="btn btn-soft-info btn-sm" data-bs-toggle="modal" data-bs-target="#auditTrailModal-product" data-audit-id="{{ $item->id }}" title="History" aria-label="History">
                                    <i class="ri-history-line align-bottom"></i>
                                </button>
                                @endcan
                                @can('inventory.view-audit')
                                    @if($item->inventory)
                                    <button type="button" class="btn btn-soft-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#auditTrailModal-inventory" data-audit-id="{{ $item->inventory->id }}" title="Stock History" aria-label="Stock History">
                                        <i class="ri-history-line align-bottom"></i>
                                    </button>
                                    @endif
                                @endcan
                                @can('spare-parts.manage')
                                <form action="{{ route('product.toggle-spare-part', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ $item->is_spare_part ? 'Unmark this as a spare part? Clients will no longer be able to request it.' : 'Mark this as a spare part? It will become requestable by clients in the portal.' }}');">
                                    @csrf
                                    <button type="submit" class="btn btn-soft-{{ $item->is_spare_part ? 'warning' : 'secondary' }} btn-sm" title="{{ $item->is_spare_part ? 'Unmark Spare Part' : 'Mark as Spare Part' }}" aria-label="{{ $item->is_spare_part ? 'Unmark Spare Part' : 'Mark as Spare Part' }}">
                                        <i class="ri-tools-fill align-bottom"></i>
                                    </button>
                                </form>
                                @endcan
                                @can('products.manage-config')
                                <a href="{{ route('standerconfig', $item->id) }}" class="btn btn-soft-secondary btn-sm" title="Standard Config" aria-label="Standard Config">
                                    <i class="ri-settings-3-line align-bottom"></i>
                                </a>
                                <a href="{{ route('TechnicalParameters', $item->id) }}" class="btn btn-soft-secondary btn-sm" title="Technical Parameters" aria-label="Technical Parameters">
                                    <i class="ri-sliders-line align-bottom"></i>
                                </a>
                                <a href="{{ route('standerconfiglist', $item->id) }}" class="btn btn-soft-secondary btn-sm" title="Standard Config List" aria-label="Standard Config List">
                                    <i class="ri-list-settings-line align-bottom"></i>
                                </a>
                                @endcan
                                <a href="{{route('product.delete' ,$item->id)}}" class="btn btn-soft-danger btn-sm" data-confirm-delete title="Delete" aria-label="Delete">
                                    <i class="ri-delete-bin-fill align-bottom"></i>
                                </a>
                            </div>
                        </td>
                        </tr>

                    @endforeach
                    </tbody>
                </table>
            </div>

        </x-ui.data-table-card>

    </div>
    <!--end col-->
</div>
<!--end row-->

<!-- Create Product (optionally with initial stock) -->
<div class="modal fade" id="exampleModalgrid" tabindex="-1" aria-labelledby="exampleModalgridLabel" aria-modal="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalgridLabel">Add Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{route('productstore')}}" method="POST" onsubmit="this.querySelector('button[type=submit]').disabled = true;">
                    @csrf
                    <div class="col-xxl-6">
                            <div>
                                <label for="firstName" class="form-label">Product Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="firstName"  name = "name" value="{{ old('name') }}" placeholder="Enter Product">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div>
                                <label for="lastName" class="form-label">unit</label>
                                <input type="text" class="form-control" id="lastName"  name = "unit" placeholder="Enter Unit">
                            </div>
                        </div><!--end col-->
                        <div class="col-xxl-6">
                            <div>
                                <label for="lastName" class="form-label">HSN</label>
                                <input type="text" class="form-control" id="lastName-2"  name = "make" placeholder="Enter make">
                            </div>
                        </div><!--end col-->
                        <div class="col-xxl-6">
                            <div>
                                <label for="emailInput" class="form-label">Rate</label>
                                <input type="text" class="form-control" id="emailInput" name = "rate" placeholder="Enter Rate">
                            </div>
                        </div><!--end col-->

                        <div class="col-xxl-12 mt-3">
                            <hr class="my-2">
                            <p class="text-muted mb-2">Initial stock <span class="fs-12">(optional — leave blank if this product isn't stock-tracked)</span></p>
                        </div>
                        <div class="col-xxl-6">
                            <div>
                                <label for="createQuantity" class="form-label">Quantity</label>
                                <input type="number" min="0" class="form-control" id="createQuantity" name="quantity" placeholder="Enter quantity in stock">
                            </div>
                        </div><!--end col-->
                        <div class="col-xxl-6">
                            <div>
                                <label for="createVendorSelect" class="form-label">Vendor</label>
                                <select class="form-select" id="createVendorSelect" name="vendor_id">
                                    <option value="">-- Not a tracked vendor --</option>
                                    @foreach($vendors as $vendor)
                                        <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div><!--end col-->
                        <div class="col-xxl-6">
                            <div>
                                <label for="createVendorName" class="form-label">Vendor Name (if not tracked above)</label>
                                <input type="text" class="form-control" id="createVendorName" name="vandername" placeholder="Enter vendor">
                            </div>
                        </div><!--end col-->

                        <div class="col-lg-12">
                            <div class="hstack gap-2 justify-content-end">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </div>
                        </div><!--end col-->
                    </div><!--end row-->
                </form>
            </div>
        </div>
    </div>
</div>

@can('products.update')
@foreach($product as $item)
<div class="modal fade" id="editProduct-{{ $item->id }}" tabindex="-1" aria-labelledby="editProduct-{{ $item->id }}-label" aria-modal="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editProduct-{{ $item->id }}-label">Edit Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('product.update', $item->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-2">
                        <label class="form-label" for="edit-product-name-{{ $item->id }}">Product Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit-product-name-{{ $item->id }}" name="name" value="{{ $item->name }}" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" for="edit-product-unit-{{ $item->id }}">Unit</label>
                        <input type="text" class="form-control" id="edit-product-unit-{{ $item->id }}" name="unit" value="{{ $item->unit }}">
                    </div>
                    <div class="mb-2">
                        <label class="form-label" for="edit-product-make-{{ $item->id }}">HSN</label>
                        <input type="text" class="form-control" id="edit-product-make-{{ $item->id }}" name="make" value="{{ $item->make }}">
                    </div>
                    <div class="mb-2">
                        <label class="form-label" for="edit-product-rate-{{ $item->id }}">Rate</label>
                        <input type="text" class="form-control" id="edit-product-rate-{{ $item->id }}" name="rate" value="{{ $item->rate }}">
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

<!-- Add Stock to an existing product -->
<div class="modal fade" id="addStockModal" tabindex="-1" aria-labelledby="addStockModalLabel" aria-modal="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addStockModalLabel">Add Stock</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{route('inventrystore')}}" method="POST">
                    @csrf
                    <input type="hidden" name="product_id" id="addStockProductId">
                    <div class="mb-3">
                        <label for="addStockProductName" class="form-label">Product</label>
                        <input type="text" class="form-control" id="addStockProductName" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="addStockQuantity" class="form-label">Quantity <span class="text-danger">*</span></label>
                        <input type="number" min="0" class="form-control" id="addStockQuantity" name="quantity" required>
                    </div>
                    <div class="mb-3">
                        <label for="addStockVendorSelect" class="form-label">Vendor</label>
                        <select class="form-select" id="addStockVendorSelect" name="vendor_id">
                            <option value="">-- Not a tracked vendor --</option>
                            @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="addStockVendorName" class="form-label">Vendor Name (if not tracked above)</label>
                        <input type="text" class="form-control" id="addStockVendorName" name="vandername" placeholder="Enter vendor">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Update (reduce) stock quantity -->
<div class="modal fade" id="updateQuantityModal" tabindex="-1" aria-labelledby="updateQuantityModalLabel" aria-modal="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateQuantityModalLabel">Update Quantity</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="updateQuantityForm">
                    <input type="hidden" id="updateQuantityInventoryId">
                    <div class="mb-3">
                        <label for="updateQuantityAmount" class="form-label">Quantity used / removed from stock <span class="text-danger">*</span></label>
                        <input type="number" min="1" class="form-control" id="updateQuantityAmount" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="submitUpdateQuantityBtn">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@can('products.view-audit')
    <x-ui.audit-trail-modal type="product" />
@endcan
@can('inventory.view-audit')
    <x-ui.audit-trail-modal type="inventory" />
@endcan
<x-ui.confirm-modal recordType="product" />
@endsection
@section('script')
<script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script src="{{ URL::asset('build/js/ui-notify.js') }}"></script>

{{-- This block only needs bootstrap (already loaded globally) - kept
     independent of the DataTables/jQuery block below so a CDN hiccup there
     can never take the Add Stock / Update Qty modals down with it. --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    @if($errors->has('name'))
        // Validation failed on the last submit - the inline error markup is
        // already in the DOM, but the modal containing the form is closed by
        // default, so without this the user never sees why nothing saved.
        new bootstrap.Modal(document.getElementById('exampleModalgrid')).show();
    @endif

    document.addEventListener('click', function (event) {
        const addStockBtn = event.target.closest('.open-add-stock-modal');
        if (addStockBtn) {
            document.getElementById('addStockProductId').value = addStockBtn.getAttribute('data-product-id');
            document.getElementById('addStockProductName').value = addStockBtn.getAttribute('data-product-name');
            new bootstrap.Modal(document.getElementById('addStockModal')).show();
            return;
        }

        const updateQtyBtn = event.target.closest('.open-update-qty-modal');
        if (updateQtyBtn) {
            document.getElementById('updateQuantityInventoryId').value = updateQtyBtn.getAttribute('data-id');
            document.getElementById('updateQuantityAmount').value = '';
            new bootstrap.Modal(document.getElementById('updateQuantityModal')).show();
        }
    });

    document.getElementById('updateQuantityForm').addEventListener('submit', function (event) {
        event.preventDefault();

        const id = document.getElementById('updateQuantityInventoryId').value;
        const quantity = document.getElementById('updateQuantityAmount').value;
        const submitBtn = document.getElementById('submitUpdateQuantityBtn');
        submitBtn.disabled = true;

        fetch('{{ route("quantityupdate") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            body: JSON.stringify({ id: id, quantity: quantity }),
        })
            .then(function (response) { return response.json().then(function (data) { return { status: response.status, data: data }; }); })
            .then(function (result) {
                if (result.status === 200 && result.data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Quantity updated',
                        showConfirmButton: false,
                        timer: 1500,
                    });
                    const modal = bootstrap.Modal.getInstance(document.getElementById('updateQuantityModal'));
                    modal.hide();
                    location.reload();
                    return;
                }

                submitBtn.disabled = false;
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: result.data.message || 'There was an issue updating the quantity. Please try again.',
                });
            })
            .catch(function () {
                submitBtn.disabled = false;
                Swal.fire({ icon: 'error', title: 'Error!', text: 'There was an issue updating the quantity. Please try again.' });
            });
    });
});
</script>

<link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" integrity="sha384-Dv1j0mqPOKbG6R+/4/adHCn5JaMBLG3iu8uTXFBM2MjEZuKwtsyLedRcRMR0cq7P" crossorigin="anonymous" rel="stylesheet" type="text/css" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js" integrity="sha384-ficRBwtap/VLzILv81vIvgp30PoJYnlCm96tPpNYHXAf+h9SIThOZxxIzRUzbpAh" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js" integrity="sha384-jIAE3P7Re8BgMkT0XOtfQ6lzZgbDw/02WeRMJvXK3WMHBNynEx5xofqia1OHuGh0" crossorigin="anonymous"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    new DataTable('#products-table', {
        language: {
            emptyTable: 'No products yet.',
            zeroRecords: 'No matching products found.',
            search: 'Search products:',
        }
    });
});
</script>
@endsection
