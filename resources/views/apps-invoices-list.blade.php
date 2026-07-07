@extends('layouts.master')
@section('title')
@lang('translation.list-view')
@endsection
@section('css')
<link href="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
<!--datatable css-->
<link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet" type="text/css" />
<!--datatable responsive css-->
<link href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" rel="stylesheet"
    type="text/css" />
<link href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css" rel="stylesheet" type="text/css" />
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
 $invoice = App\Models\Invoice::join('customer', 'invoice.id', '=', 'customer.invoice_id','left')->
        orderBy('id' ,'desc')->get(['invoice.*', 'customer.name' ,'customer.phone','customer.id as customer_id']);

?>
 <!-- end row-->

<div class="row">
    <div class="col-lg-12">
        <div class="card" id="invoiceList">
            <div class="card-header border-0">
                <div class="d-flex align-items-center">
                    <h5 class="card-title mb-0 flex-grow-1">Invoices</h5>
                    <div class="flex-shrink-0">
                        <div class="d-flex gap-2 flex-wrap">
                            <button class="btn btn-primary" id="remove-actions" onClick="deleteMultiple()"><i class="ri-delete-bin-2-line"></i></button>
                            <a href="{{route('invoice.create')}}" class="btn btn-danger"><i class="ri-add-line align-bottom me-1"></i> Create Invoice</a>
                        </div>
                    </div>
                </div>
            </div>
           
            <div class="card-body">
                <div>
                    <div class="table-responsive table-card">
                        <table id="example" class="table table-bordered dt-responsive nowrap table-striped align-middle"
                        style="width:100%">
                            <thead class="text-muted">
                                <tr>
                                    
                                    <th class="sort text-uppercase" data-sort="invoice_id">ID</th>
                                    <th class="sort text-uppercase" data-sort="customer_name">
                                        Customer</th>
                                    
                                    <th class="sort text-uppercase" data-sort="country">Phone</th>
                                    <th class="sort text-uppercase" data-sort="date">Date</th>
                                    <th class="sort text-uppercase" data-sort="invoice_amount">
                                        Amount</th>
                                    <th class="sort text-uppercase" data-sort="invoice_amount">
                                            Paid Amount</th>
                                    <th class="sort text-uppercase" data-sort="invoice_amount">
                                        Remaining Amount</th>   
                                    <th class="sort text-uppercase" data-sort="invoice_amount">
                                            Status</th>
                                    <th class="sort text-uppercase" data-sort="action">Action</th>
                                </tr>
                            </thead>
                            <tbody class="list form-check-all" id="invoice-list-data">
                                @foreach($invoice as $item)
                                 <tr>
                                  <td>{{$item->id}}</td>
                                  <td>{{$item->name}}</td>
                                  <td>{{$item->phone}}</td>
                                  <td>{{$item->date}}</td>
                                  <td>{{$item->amount}}</td>
                                  <td>{{$item->paidamount}}</td>
                                  <td>{{$item->remaining_amount}}</td>
                                  <td>
                                    @if($item->amount == $item->paidamount)
                                        <span  class = "badge bg-success-subtle text-success text-uppercase">Paid</span>
                                    @else
                                        <span  class = "badge bg-warning-subtle text-warning text-uppercase">Pending</span>
                                    @endif
                                </td>
                                <td>
                                <div class="d-flex gap-2">
                                    <div class="edit">
                                        <a href="{{route('invoice.details' ,$item->id)}}"><button class="btn btn-sm btn-success edit-item-btn">Details</button></a>
                                    </div>
                                    <div class="remove">
                                        @if($item->amount == $item->paidamount)
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-id="{{$item->id}}" id="savepayment" data-bs-target="#exampleModalgrid" style="display: none;">
            Payment
       </button> @else
       <button type="button" class="btn btn-sm btn-primary open-modal" data-id="{{ $item->id }}" data-customer="{{ $item->customer_id}}"data-bs-toggle="modal" data-bs-target="#exampleModalgrid">
        Payment
    </button>
                 @endif
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
                <div class="modal fade flip" id="deleteOrder" tabindex="-1" aria-labelledby="deleteOrderLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-body p-5 text-center">
                                <lord-icon src="https://cdn.lordicon.com/gsqxdxog.json" trigger="loop" colors="primary:#405189,secondary:#f06548" style="width:90px;height:90px">
                                </lord-icon>
                                <div class="mt-4 text-center">
                                    <h4>You are about to delete a order ?</h4>
                                    <p class="text-muted fs-15 mb-4">Deleting your order will remove
                                        all of
                                        your information from our database.</p>
                                    <div class="hstack gap-2 justify-content-center remove">
                                        <button class="btn btn-link link-success fw-medium text-decoration-none" data-bs-dismiss="modal" id="deleteRecord-close"><i class="ri-close-line me-1 align-middle"></i>
                                            Close</button>
                                        <button class="btn btn-danger" id="delete-record">Yes,
                                            Delete It</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal fade" id="exampleModalgrid" tabindex="-1" aria-labelledby="exampleModalgridLabel" aria-modal="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalgridLabel">Payment</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form id="paymentForm">
                                    <!-- Input to show the ID -->
                                    <div class="mb-3">
                                      
                                      <input type="hidden" class="form-control" id="itemIdInput" readonly>
                                      <input type="text" class="form-control" id="customerInput"  name = "customer_id"readonly>
                                      
                                    </div>
                          
                                    <!-- Input to enter the amount paid -->
                                    <div class="mb-3">
                                      <label for="paidAmountInput" class="form-label">Paid Amount</label>
                                      <input type="number" class="form-control" id="paidAmountInput" name = "paidAmount" required>
                                    </div>
                          
                                    <!-- Submit button -->
                                    <div class="modal-footer">
                                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                      <button type="submit" class="btn btn-primary" id="submitPaymentBtn">Submit Payment</button>
                                    </div>
                                  </form>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end modal -->
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
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="{{ URL::asset('build/js/pages/datatables.init.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>



<script>
   
document.addEventListener('DOMContentLoaded', function() {
    // Listen for click events on buttons with the 'open-modal' class
    const buttons = document.querySelectorAll('.open-modal');
    
    buttons.forEach(button => {
        button.addEventListener('click', function() {
            // Get the data-id from the clicked button
            const itemId = this.getAttribute('data-id');
            const customer_id = this.getAttribute('data-customer');
            
            
            // Set the value of the input field in the modal to the item ID
            document.getElementById('itemIdInput').value = itemId;
            document.getElementById('customerInput').value = customer_id;

        });
    });
});

    
document.getElementById('paymentForm').addEventListener('submit', function(event) {
        event.preventDefault();

        const itemId = document.getElementById('itemIdInput').value;
        const customer_id = document.getElementById('customerInput').value
        const paidAmount = document.getElementById('paidAmountInput').value;
        

        // Make an AJAX request to update the payment
        fetch('/update-payment', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}' // Include CSRF token for security (if using Laravel)
            },
            body: JSON.stringify({
                id: itemId,
                customer_id:customer_id,
                paidAmount: paidAmount
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show SweetAlert successss message
                Swal.fire({
                    icon: 'success',
                    title: 'Payment Updated!',
                    text: 'The payment was updated successfully.',
                    showConfirmButton: false,
                    timer: 2000
                });

                // Hide the modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('exampleModalgrid'));
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
