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

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="ri-checkbox-circle-line align-middle me-1"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="row">
    <div class="col-lg-12">
        <x-ui.data-table-card title="Products">
            <div class="d-flex gap-2 flex-wrap mb-3">
                <x-ui.button variant="primary" icon="ri-delete-bin-2-line" id="remove-actions" onclick="deleteMultiple()" ariaLabel="Delete selected products" />
                <x-ui.button variant="success" size="sm" icon="ri-add-line" data-bs-toggle="modal" data-bs-target="#exampleModalgrid">Create Product</x-ui.button>
            </div>
            <div class="table-responsive">
                <?php
                // NOT dead weight: GET /product is a single-segment path, so it is
                // currently intercepted by the catch-all route in routes/web.php
                // (registered before this route) and served via
                // HomeController::index(), which renders this view with no data at
                // all. This self-query is the only thing that makes the page work
                // today. Removing it breaks the page until that routing bug (tracked
                // separately, out of scope for this pass) is fixed. When the
                // controller *is* reached (e.g. once the routing bug is fixed), this
                // duplicates the identical query ProductController::index() already
                // runs — redundant but harmless.
                $product = App\Models\Product::orderBy('id' ,'desc')->get();
                ?>
                <table class="table table-bordered align-middle" id="products-table">
                    <thead class="text-muted">
                        <tr>
                            <th class="sort text-uppercase" data-sort="invoice_id">ID</th>
                            <th class="sort text-uppercase" data-sort="product">
                                Product</th>
                            <th class="sort text-uppercase" data-sort="make">Make</th>
                            <th class="sort text-uppercase" data-sort="rate">Rate</th>

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
                            <div class="dropdown d-inline-block">
                                <button class="btn btn-soft-secondary btn-sm dropdown" type="button"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="ri-more-fill align-middle"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">

                                    <li>
                                        <a class="dropdown-item remove-item-btn" href="{{route('product.delete' ,$item->id)}}">
                                            <i class="ri-delete-bin-fill align-bottom me-2 text-muted"></i> Delete
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </td>
                        </tr>

                    @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Modal -->
            <x-ui.confirm-modal record-type="product" />
            <!--end modal -->
        </x-ui.data-table-card>

    </div>
    <!--end col-->
</div>
<!--end row-->
<div class="modal fade" id="exampleModalgrid" tabindex="-1" aria-labelledby="exampleModalgridLabel" aria-modal="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalgridLabel">Add Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{route('productstore')}}" method="POST">
                    @csrf
                    <div class="col-xxl-6">
                            <div>
                                <label for="firstName" class="form-label">Product Name</label>
                                <input type="text" class="form-control" id="firstName"  name = "name" placeholder="Enter Product">
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
@endsection
@section('script')
<link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" integrity="sha384-Dv1j0mqPOKbG6R+/4/adHCn5JaMBLG3iu8uTXFBM2MjEZuKwtsyLedRcRMR0cq7P" crossorigin="anonymous" rel="stylesheet" type="text/css" />
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js" integrity="sha384-ficRBwtap/VLzILv81vIvgp30PoJYnlCm96tPpNYHXAf+h9SIThOZxxIzRUzbpAh" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js" integrity="sha384-jIAE3P7Re8BgMkT0XOtfQ6lzZgbDw/02WeRMJvXK3WMHBNynEx5xofqia1OHuGh0" crossorigin="anonymous"></script>
{{-- invoiceslist.init.js is kept (unlike other migrated pages) because it defines
     deleteMultiple() and the #delete-record click handler that this page's
     bulk-delete button and delete-confirmation modal (Task 4) still rely on.
     Only its list.js pagination init becomes unused here, not its handlers. --}}
<script src="{{ URL::asset('build/js/pages/invoiceslist.init.js') }}"></script>
<script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script src="{{ URL::asset('build/js/ui-notify.js') }}"></script>
<script src="{{ URL::asset('build/js/app.js') }}"></script>
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
