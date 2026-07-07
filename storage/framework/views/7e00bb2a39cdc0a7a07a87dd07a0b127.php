
<?php $__env->startSection('title'); ?>
<?php echo app('translator')->get('Standerconfig'); ?>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('css'); ?>
<link href="<?php echo e(URL::asset('build/libs/sweetalert2/sweetalert2.min.css')); ?>" rel="stylesheet" type="text/css" />
  <!--datatable css-->
  <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet" type="text/css" />
  <!--datatable responsive css-->
  <link href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" rel="stylesheet"
      type="text/css" />
  <link href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css" rel="stylesheet" type="text/css" />
  <link href="<?php echo e(URL::asset('build/libs/dropzone/dropzone.css')); ?>" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo e(URL::asset('build/libs/filepond/filepond.min.css')); ?>" type="text/css" />
    <link rel="stylesheet"
        href="<?php echo e(URL::asset('build/libs/filepond-plugin-image-preview/filepond-plugin-image-preview.min.css')); ?>">

<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
<?php $__env->startComponent('components.breadcrumb'); ?>
<?php $__env->slot('li_1'); ?>
Standerconfig
<?php $__env->endSlot(); ?>
<?php $__env->slot('title'); ?>

<?php $__env->endSlot(); ?>
<?php echo $__env->renderComponent(); ?>
<?php

 $id = 1;
 $Cnsthinks = App\Models\Cnsthinks::where('product_id' ,$id)->get();
 $Cutting = App\Models\Cutting::where('product_id' ,$id)->get();

 
?>

<div class="row">
    <div class="col-lg-12">
        <div class="card" id="invoiceList">
            <div class="card-header border-0">
                <div class="d-flex align-items-center">
                    <h5 class="card-title mb-0 flex-grow-1">Technical-Parameters</h5>
                    
                </div>
            </div>
            <div class="card-body bg-light-subtle border border-dashed border-start-0 border-end-0">
                <div class="row">
                    <div class="col-lg-3">
                        <div class="nav nav-pills flex-column nav-pills-tab custom-verti-nav-pills text-center" role="tablist" aria-orientation="vertical">
                            <a class="nav-link active show" id="custom-v-pills-home-tab" data-bs-toggle="pill" href="#custom-v-pills-home" role="tab" aria-controls="custom-v-pills-home"
                                aria-selected="true">
                                <i class="ri-home-4-line d-block fs-20 mb-1"></i>
                            Cutting Way
                                Details</a>
                            <a class="nav-link" id="custom-v-pills-profile-tab" data-bs-toggle="pill" href="#custom-v-pills-profile" role="tab" aria-controls="custom-v-pills-profile"
                                aria-selected="false">
                                <i class="ri-user-2-line d-block fs-20 mb-1"></i>
                              CNC Thickness
                                Details</a>
                           
                               
                        </div>
                    </div> <!-- end col-->
                    <div class="col-lg-9">
                        <div class="tab-content text-muted mt-3 mt-lg-0">
                            <div class="tab-pane fade active show" id="custom-v-pills-home" role="tabpanel" aria-labelledby="custom-v-pills-home-tab">
                               <div class = "row">
                                <div class ="col-md-6">
                                </div>
                                <div class = "col-md-6" style = "text-align:right">
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModalgrid" id= "modal" data-id = "1">
                                        Add Cutting Way Details
                                       </button>
                                </div>
                               </div>
                               <br>
                               <div class = "row">
                                <table id="example" class="table table-bordered dt-responsive nowrap table-striped align-middle"
                                style="width:100%">
                                <thead>
                                    <tr>
                                        
                                        <th data-ordering="false">SR No.</th>
                                        
                                        <th data-ordering="false">Product</th>
                                        
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $Cutting; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        
                                        <td><?php echo e($item->id); ?></td>
                                        <td><?php echo e($item->cuttingway); ?></td>
                                        
                                        <td>
                                            <div class="dropdown d-inline-block">
                                                <button class="btn btn-soft-secondary btn-sm dropdown" type="button"
                                                    data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="ri-more-fill align-middle"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li><a class="dropdown-item edit-item-btn"><i
                                                                class="ri-pencil-fill align-bottom me-2 text-muted"></i> Edit</a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item remove-item-btn">
                                                            <i class="ri-delete-bin-fill align-bottom me-2 text-muted"></i> Delete
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                   <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                       
                                </tbody>
                            </table>
                               </div>
                                
                            </div><!--end tab-pane-->
                            <div class="tab-pane fade" id="custom-v-pills-profile" role="tabpanel" aria-labelledby="custom-v-pills-profile-tab">
                                <div class = "row">
                                    <div class ="col-md-6">
                                    </div>
                                    <div class = "col-md-6" style = "text-align:right">
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModalgrid1" id= "modal1" data-id = "1">
                                            Add CNC Thickness Details
                                           </button>
                                    </div>
                                   </div>
                                   <br>
                                   <div class = "row">
                                    <table id="example1" class="table table-bordered dt-responsive nowrap table-striped align-middle"
                                    style="width:100%">
                                    <thead>
                                        <tr>
                                            
                                            <th data-ordering="false">SR No.</th>
                                            
                                            <th data-ordering="false">Product</th>
                                            
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $__currentLoopData = $Cnsthinks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item1): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            
                                            <td><?php echo e($item1->id); ?></td>
                                            <td><?php echo e($item1->cnsthinks); ?></td>
                                            
                                            <td>
                                                <div class="dropdown d-inline-block">
                                                    <button class="btn btn-soft-secondary btn-sm dropdown" type="button"
                                                        data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="ri-more-fill align-middle"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        <li>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item remove-item-btn">
                                                                <i class="ri-delete-bin-fill align-bottom me-2 text-muted"></i> Delete
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                       <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                       
                                           
                                    </tbody>
                                </table>
                                   </div>
                            </div><!--end tab-pane-->
                           
                           
                        </div>
                    </div> <!-- end col-->
                </div> <!-- end row-->
            </div>
           
        </div>

    </div>
    <!--end col-->
</div>
<!--end row-->
<div class="modal fade" id="exampleModalgrid" tabindex="-1" aria-labelledby="exampleModalgridLabel" aria-modal="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalgridLabel">Cutting Way</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="<?php echo e(route('cuttingwaystore')); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class ="row">
                        <div class ="col-md-3">
                            <label for="firstName" class="form-label">Cutting Way</label> 
                            <input type="text" class="form-control" id="product_id"  name = "product_id" placeholder="Enter Product">
                            <input type="text" class="form-control" id="firstName"  name = "cuttingway" placeholder="Enter Product">
                        </div>
                        
                    </div>

                    
                    <br>
                       
                       
                       
                        
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

<div class="modal fade" id="exampleModalgrid1" tabindex="-1" aria-labelledby="exampleModalgridLabel" aria-modal="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalgridLabel">CNC thinkness</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="<?php echo e(route('cncthinknessstore')); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class ="row">
                        <div class ="col-md-3">
                            <label for="firstName" class="form-label">CNC thinkness</label> 
                            <input type="text" class="form-control" id="product_id1"  name = "product_id" placeholder="Enter Product">
                            <input type="text" class="form-control" id="firstName"  name = "cuttingthinks" placeholder="Enter Product">
                        </div>
                        
                    </div>

                    
                    <br>
                       
                       
                       
                        
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


<?php $__env->stopSection(); ?>
<?php $__env->startSection('script'); ?>
<script src="<?php echo e(URL::asset('build/libs/list.js/list.min.js')); ?>"></script>
<script src="<?php echo e(URL::asset('build/libs/list.pagination.js/list.pagination.min.js')); ?>"></script>
<script src="<?php echo e(URL::asset('build/js/pages/invoiceslist.init.js')); ?>"></script>
<script src="<?php echo e(URL::asset('build/libs/sweetalert2/sweetalert2.min.js')); ?>"></script>
<script src="<?php echo e(URL::asset('build/js/app.js')); ?>"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"
integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
<script src="<?php echo e(URL::asset('build/js/pages/datatables.init.js')); ?>"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
<script src="<?php echo e(URL::asset('build/libs/dropzone/dropzone-min.js')); ?>"></script>
    <script src="<?php echo e(URL::asset('build/libs/filepond/filepond.min.js')); ?>"></script>
    <script src="<?php echo e(URL::asset('build/libs/filepond-plugin-image-preview/filepond-plugin-image-preview.min.js')); ?>">
    </script>
    <script
        src="<?php echo e(URL::asset('build/libs/filepond-plugin-file-validate-size/filepond-plugin-file-validate-size.min.js')); ?>">
    </script>
    <script
        src="<?php echo e(URL::asset('build/libs/filepond-plugin-image-exif-orientation/filepond-plugin-image-exif-orientation.min.js')); ?>">
    </script>
    <script src="<?php echo e(URL::asset('build/libs/filepond-plugin-file-encode/filepond-plugin-file-encode.min.js')); ?>"></script>

    <script src="<?php echo e(URL::asset('build/js/pages/form-file-upload.init.js')); ?>"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script>
    $(document).ready(function(){
        $('#modal').click(function() {
            var password =  1
         $('#product_id').val(password) 
           
    });
    });
    </script>
    <script>
        $(document).ready(function(){
            $('#modal1').click(function() {
                var password =  1
             $('#product_id1').val(password) 
               
        });
        });
        </script>
        
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/u411614341/domains/cms.oraclemachinetech.com/public_html/resources/views/technicalparameters.blade.php ENDPATH**/ ?>