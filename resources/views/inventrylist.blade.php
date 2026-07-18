@extends('layouts.master')
@section('title')
@lang('translation.list-view')
@endsection
@section('css')
<link href="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
<!--datatable css-->
<link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" integrity="sha384-Dv1j0mqPOKbG6R+/4/adHCn5JaMBLG3iu8uTXFBM2MjEZuKwtsyLedRcRMR0cq7P" crossorigin="anonymous" rel="stylesheet" type="text/css" />
<!--datatable responsive css-->
<link href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" integrity="sha384-yeqtDRnzRLecfhe1TzrbgNOTB74vG/UQ0vsFfHPCXe5rnFfjTj3GIfBHCMlUGCh5" crossorigin="anonymous" rel="stylesheet"
    type="text/css" />
<link href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css" integrity="sha384-/m1O+MBcLoqe7amEwkvENLD+f6ePq8wBTHBWz+Kf1zp90kmGlto3IFhhNa9fZllu" crossorigin="anonymous" rel="stylesheet" type="text/css" />
@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1')
invoices
@endslot
@slot('title')
list view
@endslot
@endcomponent
<?php
$Invetry = App\Models\Invetry::leftJoin('product', 'invetry.product_id', '=', 'product.id')
    ->orderBy('invetry.id', 'desc')
    ->get(['invetry.*', 'product.name as product_name']);


$product = App\Models\Product::orderBy('id' ,'desc')->get();


        ?>
 <!-- end row-->

<div class="row">
    <div class="col-lg-12">
        <div class="card" id="invoiceList">
            <div class="card-header border-0">
                <div class="d-flex align-items-center">
                    <h5 class="card-title mb-0 flex-grow-1">inventrylist</h5>
                    <div class="flex-shrink-0">
                        <div class="d-flex gap-2 flex-wrap">
                            <x-ui.button variant="primary" icon="ri-delete-bin-2-line" id="remove-actions" onclick="deleteMultiple()" ariaLabel="Delete selected items" />

                        </div>
                    </div>
                </div>
            </div>
            <div class = "row">
                <div class = "col-md-6" style="text-align:right;margin-left: 666px;">
                <x-ui.button variant="success" icon="ri-add-line" data-bs-toggle="modal" data-bs-target="#exampleModalgrid">Create Inventry</x-ui.button>
                </div>
            </div>
           <br>
            <div class="card-body">
                <div>
                    <div class="table-responsive table-card">
                        <table id="example" class="table table-bordered dt-responsive nowrap table-striped align-middle"
                        style="width:100%">
                            <thead class="text-muted">
                                <tr>
                                    
                                    <th class="sort text-uppercase" data-sort="invoice_id">ID</th>
                                    <th class="sort text-uppercase" data-sort="Product_Name">
                                        Product Name</th>
                                    <th class="sort text-uppercase" data-sort="Quantity">
                                        Quantity</th>
                                    <th class="sort text-uppercase" data-sort="Vander_name">
                                                Vander Name</th>
                                    <th class="sort text-uppercase" data-sort="invoice_amount">
                                                Action</th>
                                    
                                    
                                    
                                    
                                    

                                </tr>
                            </thead>
                            <tbody class="list form-check-all" id="invoice-list-data">
                                @foreach($Invetry as $item)
                                 <tr>
                                  <td>{{$item->id}}</td>
                                  <td>{{$item->product_name}}</td>
                                  <td>{{$item->quantity}}</td>
                                  <td>{{$item->vandername}}</td>
                                  <td>
                                    <div class="d-flex gap-2">
                                     <div class="remove">
                                     <button type="button" class="btn btn-sm btn-primary open-modal" data-id="{{ $item->id }}"data-bs-toggle="modal" data-bs-target="#exampleModalgrid1">
                                        Quantity Update
                                    </button>
                                    </div>
                                    </div>
                                    </td>
                                 </tr>
                                @endforeach
                            </tbody>
                        </table>
                       
                        
                    </div>
                    
                </div>

                <!-- Modal -->
                <x-ui.confirm-modal record-type="inventory item" />
                <div class="modal fade" id="exampleModalgrid" tabindex="-1" aria-labelledby="exampleModalgridLabel" aria-modal="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalgridLabel">Add Product</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form action="{{route('inventrystore')}}" method="POST">
                                    @csrf
                                    <div class="col-xxl-6">
                                            <div>
                                                <label for="firstName" class="form-label">Product</label>
                                                 
                                                 <select class="form-select" id="product_id" name = "product_id">
                                                    
                                                    <option selected>Choose...</option>
                                                    @forEach($product as $item)
                                                   
                                                    <option value="{{$item->id}}">{{$item->name}}</option>
                                                    @endforeach
                                                 </select>
                                                
                                            </div>
                                            <div>
                                                <label for="lastName" class="form-label">Quantity</label>
                                                <input type="text" class="form-control" id="quantity"  name = "quantity" placeholder="Enter Unit">
                                            </div>
                                        </div><!--end col-->
                                        <div class="col-xxl-6">
                                            <div>
                                                <label for="lastName" class="form-label">Vander Name</label>
                                                <input type="text" class="form-control" id="vandername"  name = "vandername" placeholder="Enter make">
                                            </div>
                                        </div><!--end col-->
                                        <div class="col-xxl-6">
                                            <div>
                                                <label for="emailInput" class="form-label">Rate</label>
                                                <input type="text" class="form-control" id="rate" name = "rate" placeholder="Enter Rate">
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
                <!--end modal -->
                <div class="modal fade" id="exampleModalgrid1" tabindex="-1" aria-labelledby="exampleModalgrid1Label" aria-modal="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalgrid1Label">Payment</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form id="paymentForm">
                                    <!-- Input to show the ID -->
                                    <div class="mb-3">
                                      <input type="hidden" class="form-control" id="itemIdInput" readonly>
                                    </div>
                                    <!-- Input to enter the amount paid -->
                                    <div class="mb-3">
                                      <label for="quantityInput" class="form-label">Quantity</label>
                                      <input type="number" class="form-control" id="quantityInput" name = "quantity" required>
                                    </div>
                          
                                    <!-- Submit button -->
                                    <div class="modal-footer">
                                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                      <button type="submit" class="btn btn-primary" id="submitPaymentBtn">Submit</button>
                                    </div>
                                  </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <!--end col-->
</div>
<!--end row-->
@endsection
@section('script')
<script src="{{ URL::asset('build/libs/list.js/list.min.js') }}"></script>
<script src="{{ URL::asset('build/libs/list.pagination.js/list.pagination.min.js') }}"></script>
{{-- <script src="{{ URL::asset('build/js/pages/invoiceslist.init.js') }}"></script> --}}

<script src="{{ URL::asset('build/js/app.js') }}"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"
integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js" integrity="sha384-ficRBwtap/VLzILv81vIvgp30PoJYnlCm96tPpNYHXAf+h9SIThOZxxIzRUzbpAh" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js" integrity="sha384-jIAE3P7Re8BgMkT0XOtfQ6lzZgbDw/02WeRMJvXK3WMHBNynEx5xofqia1OHuGh0" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js" integrity="sha384-ziUH70yXeghwn7LIJvtjobzpllxs+w4FJL4/ssbFYWoYof46CveVyQ+GCaR1eTXj" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js" integrity="sha384-WeURKcdkISkUjHZ77+LXthyEWYJMabWHrb2WEz925E9WM6I+yjuZRvUu5l21xbGT" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js" integrity="sha384-mOGjUrCoMJ8/pGqc8SQHuJdYPrdB9cjSkiuLQbw6D7orbJyMkk6xYDlYtkEH051d" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js" integrity="sha384-0RqPR2pS8PZtdf2dUWHpI+Um4DsvovzRAZPEZg7lXp4/HhxUBq0Y8VPYBE902L4u" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js" integrity="sha384-UAA3vlTPq9dwxB61awBFhR7Y5uBFOKQWuZueu4C6uI48gjIoqI/OTmYWEYWZXbGR" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js" integrity="sha384-HUHsYVOhSyHyZRTWv8zkbKVk7Xmg12CCNfKEUJ7cSuW/22Lz3BITd3Om6QeiXICb" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js" integrity="sha384-v9EFJbsxLXyYar8TvBV8zu5USBoaOC+ZB57GzCmQiWfgDIjS+wANZMP5gjwMLwGv" crossorigin="anonymous"></script>
<script src="{{ URL::asset('build/js/pages/datatables.init.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.26.25" integrity="sha384-nLoOnA/BDh8A/jxqtckg4DumuCGOBYUnNJLZdQz/zfYNp3wcjGSoWTAzgko06G/2" crossorigin="anonymous"></script>



<script>
   
document.addEventListener('DOMContentLoaded', function() {
    // Listen for click events on buttons with the 'open-modal' class
    const buttons = document.querySelectorAll('.open-modal');
    buttons.forEach(button => {
        button.addEventListener('click', function() {
            // Get the data-id from the clicked button
            const itemId = this.getAttribute('data-id');
            
            // Set the value of the input field in the modal to the item ID
            document.getElementById('itemIdInput').value = itemId;
        });
    });
});

    
document.getElementById('paymentForm').addEventListener('submit', function(event) {

        event.preventDefault();

        const itemId = document.getElementById('itemIdInput').value;
        const quantity = document.getElementById('quantityInput').value;
       
        
        // Make an AJAX request to update the payment
        fetch('/quantityupdate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}' // Include CSRF token for security (if using Laravel)
            },
            body: JSON.stringify({
                id: itemId,
                quantity: quantity
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show SweetAlert successss message
                Swal.fire({
                    icon: 'success',
                    title: 'Quantity',
                    text: 'The quantity was updated successfully.',
                    showConfirmButton: false,
                    timer: 2000
                });

                // Hide the modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('exampleModalgrid1'));
                modal.hide();
                location.reload();
            } else {
                // Show SweetAlert error message
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'There was an issue updating the payment. Please try again.',
                });
            }
        })
        // .catch(error => {
        //     console.error('Error:', error);

        //     // Show SweetAlert error message in case of a request failure
        //     Swal.fire({
        //         icon: 'error',
        //         title: 'Error!',
        //         text: 'An unexpected error occurred. Please try again later.',
        //     });
        // });
    });

    </script>

   
@endsection
