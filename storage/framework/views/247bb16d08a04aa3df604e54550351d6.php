
<?php $__env->startSection('title'); ?>
<?php echo app('translator')->get('translation.list-view'); ?>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('css'); ?>
<link href="<?php echo e(URL::asset('build/libs/sweetalert2/sweetalert2.min.css')); ?>" rel="stylesheet" type="text/css" />
<!--datatable css-->
<link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet" type="text/css" />
<!--datatable responsive css-->
<link href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" rel="stylesheet"
    type="text/css" />
<link href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css" rel="stylesheet" type="text/css" />
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
<?php $__env->startComponent('components.breadcrumb'); ?>
<?php $__env->slot('li_1'); ?>
invoices
<?php $__env->endSlot(); ?>
<?php $__env->slot('title'); ?>
list view
<?php $__env->endSlot(); ?>
<?php echo $__env->renderComponent(); ?>
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
                            <button class="btn btn-primary" id="remove-actions" onClick="deleteMultiple()"><i class="ri-delete-bin-2-line"></i></button>
                            
                        </div>
                    </div>
                </div>
            </div>
            <div class = "row">
                <div class = "col-md-6" style="text-align:right;margin-left: 666px;">
                <button type="button" class="btn btn-primary btn-md" data-bs-toggle="modal" data-bs-target="#exampleModalgrid">Create Inventry</button>
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
                                <?php $__currentLoopData = $Invetry; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                 <tr>
                                  <td><?php echo e($item->id); ?></td>
                                  <td><?php echo e($item->product_name); ?></td>
                                  <td><?php echo e($item->quantity); ?></td>
                                  <td><?php echo e($item->vandername); ?></td>
                                  <td>
                                    <div class="d-flex gap-2">
                                     <div class="remove">
                                     <button type="button" class="btn btn-sm btn-primary open-modal" data-id="<?php echo e($item->id); ?>"data-bs-toggle="modal" data-bs-target="#exampleModalgrid1">
                                        Quantity Update
                                    </button>  
                                    </div>
                                    </div>
                                    </td>
                                 </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
                                <h5 class="modal-title" id="exampleModalgridLabel">Add Product</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form action="<?php echo e(route('inventrystore')); ?>" method="POST">
                                    <?php echo csrf_field(); ?>
                                    <div class="col-xxl-6">
                                            <div>
                                                <label for="firstName" class="form-label">Product</label>
                                                 
                                                 <select class="form-select" id="product_id" name = "product_id">
                                                    
                                                    <option selected>Choose...</option>
                                                    <?php $__currentLoopData = $product; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                   
                                                    <option value="<?php echo e($item->id); ?>"><?php echo e($item->name); ?></option>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
                <div class="modal fade" id="exampleModalgrid1" tabindex="-1" aria-labelledby="exampleModalgridLabel" aria-modal="true">
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
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script'); ?>
<script src="<?php echo e(URL::asset('build/libs/list.js/list.min.js')); ?>"></script>
<script src="<?php echo e(URL::asset('build/libs/list.pagination.js/list.pagination.min.js')); ?>"></script>


<script src="<?php echo e(URL::asset('build/js/app.js')); ?>"></script>
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
<script src="<?php echo e(URL::asset('build/js/pages/datatables.init.js')); ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>



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
                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>' // Include CSRF token for security (if using Laravel)
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

   
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/u411614341/domains/cms.oraclemachinetech.com/public_html/resources/views/inventrylist.blade.php ENDPATH**/ ?>