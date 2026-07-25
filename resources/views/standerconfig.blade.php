@extends('layouts.master')
@section('title')
Standard Config
@endsection
@section('css')
<link href="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
  <!--datatable css-->
  <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" integrity="sha384-Dv1j0mqPOKbG6R+/4/adHCn5JaMBLG3iu8uTXFBM2MjEZuKwtsyLedRcRMR0cq7P" crossorigin="anonymous" rel="stylesheet" type="text/css" />
  <!--datatable responsive css-->
  <link href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" integrity="sha384-yeqtDRnzRLecfhe1TzrbgNOTB74vG/UQ0vsFfHPCXe5rnFfjTj3GIfBHCMlUGCh5" crossorigin="anonymous" rel="stylesheet"
      type="text/css" />
  <link href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css" integrity="sha384-/m1O+MBcLoqe7amEwkvENLD+f6ePq8wBTHBWz+Kf1zp90kmGlto3IFhhNa9fZllu" crossorigin="anonymous" rel="stylesheet" type="text/css" />
  <link href="{{ URL::asset('build/libs/dropzone/dropzone.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ URL::asset('build/libs/filepond/filepond.min.css') }}" type="text/css" />
    <link rel="stylesheet"
        href="{{ URL::asset('build/libs/filepond-plugin-image-preview/filepond-plugin-image-preview.min.css') }}">

@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1')
Standard Config
@endslot
@slot('title')

@endslot
@endcomponent

<x-ui.back-link :route="route('product')" label="Back to Products" />
<div class="row">
    <div class="col-lg-12">
        <div class="card" id="invoiceList">
            <div class="card-header border-0">
                <div class="d-flex align-items-center">
                    <h5 class="card-title mb-0 flex-grow-1">Standard Config</h5>
                    
                </div>
            </div>
            <div class="card-body bg-light-subtle border border-dashed border-start-0 border-end-0">
                <div class="row">
                    <div class="col-lg-3">
                        <div class="nav nav-pills flex-column nav-pills-tab custom-verti-nav-pills text-center" role="tablist" aria-orientation="vertical">
                            <a class="nav-link active show" id="custom-v-pills-home-tab" data-bs-toggle="pill" href="#custom-v-pills-home" role="tab" aria-controls="custom-v-pills-home"
                                aria-selected="true">
                                <i class="ri-home-4-line d-block fs-20 mb-1"></i>
                                software Details</a>
                            <a class="nav-link" id="custom-v-pills-profile-tab" data-bs-toggle="pill" href="#custom-v-pills-profile" role="tab" aria-controls="custom-v-pills-profile"
                                aria-selected="false">
                                <i class="ri-user-2-line d-block fs-20 mb-1"></i>
                                Laser Controller</a>
                            <a class="nav-link" id="custom-v-pills-messages-tab" data-bs-toggle="pill" href="#custom-v-pills-messages" role="tab" aria-controls="custom-v-pills-messages"
                                aria-selected="false">
                                <i class="ri-mail-line d-block fs-20 mb-1"></i>
                                Focusing Laser Cutting Head</a>
                                <a class="nav-link" id="custom-v-pills-custom-tab" data-bs-toggle="pill" href="#custom-v-pills-custom" role="tab" aria-controls="custom-v-pills-custom"
                                aria-selected="false">
                                <i class="ri-mail-line d-block fs-20 mb-1"></i>
                                Power
                                Source</a>
                        </div>
                    </div> <!-- end col-->
                    <div class="col-lg-9">
                        <div class="tab-content text-muted mt-3 mt-lg-0">
                            <div class="tab-pane fade active show" id="custom-v-pills-home" role="tabpanel" aria-labelledby="custom-v-pills-home-tab">
                               <div class = "row">
                                <div class ="col-md-6">
                                </div>
                                <div class = "col-md-6" style = "text-align:right">
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModalgrid" data-id = "1" id ="modal1">
                                        Create software
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
                                        <th data-ordering="false">Company</th>
                                        <th data-ordering="false">Product</th>
                                        <th data-ordering="false">Logo</th>
                                        <th data-ordering="false">Image</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($softwere as $item)
                                    <tr>
                                        
                                        <td>{{$item->id}}</td>
                                        <td>{{$item->company}}</td>
                                        <td>{{$item->product}}</td>
                                        <td>{{$item->logo}}</td>
                                        <td>{{$item->image}}</td>
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
                                   @endforeach
                                       
                                </tbody>
                            </table>
                               </div>
                                
                            </div><!--end tab-pane-->
                            <div class="tab-pane fade" id="custom-v-pills-profile" role="tabpanel" aria-labelledby="custom-v-pills-profile-tab">
                                <div class = "row">
                                    <div class ="col-md-6">
                                    </div>
                                    <div class = "col-md-6" style = "text-align:right">
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModalgrid1">
                                            Create laser Cutting Machine
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
                                            <th data-ordering="false">Company</th>
                                            <th data-ordering="false">Product</th>
                                            <th data-ordering="false">Logo</th>
                                            <th data-ordering="false">Image</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($Lasercutting as $item1)
                                        <tr>
                                            
                                            <td>{{$item1->id}}</td>
                                            <td>{{$item1->company}}</td>
                                            <td>{{$item1->product}}</td>
                                            <td>{{$item1->logo}}</td>
                                            <td>{{$item1->image}}</td>
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
                                       @endforeach
                                       
                                           
                                    </tbody>
                                </table>
                                   </div>
                            </div><!--end tab-pane-->
                            <div class="tab-pane fade" id="custom-v-pills-messages" role="tabpanel" aria-labelledby="custom-v-pills-messages-tab">
                                <div class = "row">
                                    <div class ="col-md-6">
                                    </div>
                                    <div class = "col-md-6" style = "text-align:right">
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModalgrid2">
                                            Create Focusing Laser Cutting Head
                                           </button>
                                    </div>
                                   </div>
                                   <br>
                                   <div class = "row">
                                    <table id="example2" class="table table-bordered dt-responsive nowrap table-striped align-middle"
                                    style="width:100%">
                                    <thead>
                                        <tr>
                                            
                                            <th data-ordering="false">SR No.</th>
                                            <th data-ordering="false">Company</th>
                                            <th data-ordering="false">Product</th>
                                            <th data-ordering="false">Logo</th>
                                            <th data-ordering="false">Image</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($Focusing as $item2)
                                        <tr>
                                            
                                            <td>{{$item2->id}}</td>
                                            <td>{{$item2->company}}</td>
                                            <td>{{$item2->product}}</td>
                                            <td>{{$item2->logo}}</td>
                                            <td>{{$item2->image}}</td>
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
                                       @endforeach
                                       
                                           
                                    </tbody>
                                </table>
                                   </div>
                            </div><!--end tab-pane-->
                            <div class="tab-pane fade" id="custom-v-pills-custom" role="tabpanel" aria-labelledby="custom-v-pills-custom-tab">
                                <div class = "row">
                                    <div class ="col-md-6">
                                    </div>
                                    <div class = "col-md-6" style = "text-align:right">
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModalgrid3">
                                            Create Power Source
                                           </button>
                                    </div>
                                   </div>
                                   <br>
                                   <div class = "row">
                                    <table id="example3" class="table table-bordered dt-responsive nowrap table-striped align-middle"
                                    style="width:100%">
                                    <thead>
                                        <tr>
                                            
                                            <th data-ordering="false">SR No.</th>
                                            <th data-ordering="false">Company</th>
                                            <th data-ordering="false">Product</th>
                                            <th data-ordering="false">Logo</th>
                                            <th data-ordering="false">Image</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($power as $item3)
                                        <tr>
                                            
                                            <td>{{$item3->id}}</td>
                                            <td>{{$item3->company}}</td>
                                            <td>{{$item3->product}}</td>
                                            <td>{{$item3->logo}}</td>
                                            <td>{{$item3->image}}</td>
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
                                       @endforeach
                                       
                                           
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
                <h5 class="modal-title" id="exampleModalgridLabel">Add Softwere</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{route('softerwerestore')}}" method="POST">
                    @csrf
                    <div class ="row">
                        <div class ="col-md-3">
                            <label for="firstName" class="form-label">Company Name</label>
                            <input type="text" class="form-control" id="firstName"  name = "company" placeholder="Enter Product">
                            <input type="text" class="form-control" id="product_id"  name = "product_id" placeholder="Enter Product">
                        </div>
                        <div class ="col-md-3">
                            <label for="firstName" class="form-label">Modal Name</label>
                            <input type="text" class="form-control" id="firstName-2"  name = "modal" placeholder="Enter Product">
                        </div>
                        <div class ="col-md-3">
                            <label for="firstName" class="form-label">Logo</label>
                            <input type="file" class="filepond filepond-input-multiple" name = "logo" multiple name="filepond"
                            data-allow-reorder="true" data-max-file-size="3MB" data-max-files="3">
                        </div>
                        <div class ="col-md-3">
                            <label for="firstName" class="form-label">images</label>
                            <input type="file" class="filepond filepond-input-multiple" name = "image" multiple name="filepond"
                            data-allow-reorder="true" data-max-file-size="3MB" data-max-files="3">
                        </div>
                    </div>

                    <div class ="row">
                        <div class ="col-md-12">
                            <label for="firstName" class="form-label">Product Details</label>
                            <textarea class="form-control bg-light border-0" id="companyAddress" rows="3" placeholder="Company Address" name = "details"></textarea>
                        </div>
                    </div>
                    <br>
                       
                       
                       
                        
                        <div class="col-lg-12">
                            @include('partials.modal-footer-buttons')
                        </div><!--end col-->
                    </div><!--end row-->
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="exampleModalgrid1" tabindex="-1" aria-labelledby="exampleModalgrid1Label" aria-modal="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalgrid1Label">Laser Cutting Machine</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{route('cuttingstore')}}" method="POST">
                    @csrf
                    <div class ="row">
                        <div class ="col-md-3">
                            <label for="firstName" class="form-label">Company Name</label>
                            <input type="text" class="form-control" id="firstName-3"  name = "company" placeholder="Enter Product">
                            <input type="text" class="form-control" id="product_id1"  name = "product_id" placeholder="Enter Product">
                        </div>
                        <div class ="col-md-3">
                            <label for="firstName" class="form-label">Modal Name</label>
                            <input type="text" class="form-control" id="firstName-4"  name = "modal" placeholder="Enter Product">
                        </div>
                        <div class ="col-md-3">
                            <label for="firstName" class="form-label">Logo</label>
                            <input type="file" class="filepond filepond-input-multiple" name = "logo" multiple name="filepond"
                            data-allow-reorder="true" data-max-file-size="3MB" data-max-files="3">
                        </div>
                        <div class ="col-md-3">
                            <label for="firstName" class="form-label">images</label>
                            <input type="file" class="filepond filepond-input-multiple" name = "image" multiple name="filepond"
                            data-allow-reorder="true" data-max-file-size="3MB" data-max-files="3">
                        </div>
                    </div>

                    <div class ="row">
                        <div class ="col-md-12">
                            <label for="firstName" class="form-label">Product Details</label>
                            <textarea class="form-control bg-light border-0" id="companyAddress-2" rows="3" placeholder="Company Address" name = "decription"></textarea>
                        </div>
                    </div>
                    <br>
                       
                       
                       
                        
                        <div class="col-lg-12">
                            @include('partials.modal-footer-buttons')
                        </div><!--end col-->
                    </div><!--end row-->
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="exampleModalgrid2" tabindex="-1" aria-labelledby="exampleModalgrid2Label" aria-modal="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalgrid2Label">Focusing Laser Cutting Head</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{route('focusingstore')}}" method="POST">
                    @csrf
                    <div class ="row">
                        <div class ="col-md-3">
                            <label for="firstName" class="form-label">Company Name</label>
                            <input type="text" class="form-control" id="firstName-5"  name = "companyname" placeholder="Enter Product">
                            <input type="text" class="form-control" id="product_id2"  name = "product_id" placeholder="Enter Product">
                        </div>
                        <div class ="col-md-3">
                            <label for="firstName" class="form-label">Modal Name</label>
                            <input type="text" class="form-control" id="firstName-6"  name = "modal" placeholder="Enter Product">
                        </div>
                        <div class ="col-md-3">
                            <label for="firstName" class="form-label">Logo</label>
                            <input type="file" class="filepond filepond-input-multiple" name = "logo" multiple name="filepond"
                            data-allow-reorder="true" data-max-file-size="3MB" data-max-files="3">
                        </div>
                        <div class ="col-md-3">
                            <label for="firstName" class="form-label">images</label>
                            <input type="file" class="filepond filepond-input-multiple" name = "image" multiple name="filepond"
                            data-allow-reorder="true" data-max-file-size="3MB" data-max-files="3">
                        </div>
                    </div>

                    <div class ="row">
                        <div class ="col-md-12">
                            <label for="firstName" class="form-label">Product Details</label>
                            <textarea class="form-control bg-light border-0" id="companyAddress-3" rows="3" placeholder="Company Address" name = "details"></textarea>
                        </div>
                    </div>
                    
                    <br>
                       
                       
                       
                        
                        <div class="col-lg-12">
                            @include('partials.modal-footer-buttons')
                        </div><!--end col-->
                    </div><!--end row-->
                </form>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="exampleModalgrid3" tabindex="-1" aria-labelledby="exampleModalgrid3Label" aria-modal="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalgrid3Label">Power Source</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{route('powerstore')}}" method="POST">
                    @csrf
                    <div class ="row">
                        <div class ="col-md-3">
                            <label for="firstName" class="form-label">Company Name</label>
                            <input type="text" class="form-control" id="firstName-7"  name = "companyname" placeholder="Enter Product">
                            <input type="text" class="form-control" id="product_id3"  name = "product_id" placeholder="Enter Product">
                        </div>
                        <div class ="col-md-3">
                            <label for="firstName" class="form-label">Modal Name</label>
                            <input type="text" class="form-control" id="firstName-8"  name = "modal" placeholder="Enter Product">
                        </div>
                        <div class ="col-md-3">
                            <label for="firstName" class="form-label">Logo</label>
                            <input type="file" class="filepond filepond-input-multiple" name = "logo" multiple name="filepond"
                            data-allow-reorder="true" data-max-file-size="3MB" data-max-files="3">
                        </div>
                        <div class ="col-md-3">
                            <label for="firstName" class="form-label">images</label>
                            <input type="file" class="filepond filepond-input-multiple" name = "image" multiple name="filepond"
                            data-allow-reorder="true" data-max-file-size="3MB" data-max-files="3">
                        </div>
                    </div>

                    <div class ="row">
                        <div class ="col-md-12">
                            <label for="firstName" class="form-label">Product Details</label>
                            <textarea class="form-control bg-light border-0" id="companyAddress-4" rows="3" placeholder="Company Address" name = "details"></textarea>
                        </div>
                    </div>
                    
                    <br>
                       <div class="col-lg-12">
                            @include('partials.modal-footer-buttons')
                        </div><!--end col-->
                    </div><!--end row-->
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
@section('script')
<script src="{{ URL::asset('build/libs/list.js/list.min.js') }}"></script>
<script src="{{ URL::asset('build/libs/list.pagination.js/list.pagination.min.js') }}"></script>
<script src="{{ URL::asset('build/js/pages/invoiceslist.init.js') }}"></script>
<script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"
integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
<script src="{{ URL::asset('build/js/pages/datatables.init.js') }}"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js" integrity="sha384-ficRBwtap/VLzILv81vIvgp30PoJYnlCm96tPpNYHXAf+h9SIThOZxxIzRUzbpAh" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js" integrity="sha384-jIAE3P7Re8BgMkT0XOtfQ6lzZgbDw/02WeRMJvXK3WMHBNynEx5xofqia1OHuGh0" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js" integrity="sha384-ziUH70yXeghwn7LIJvtjobzpllxs+w4FJL4/ssbFYWoYof46CveVyQ+GCaR1eTXj" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js" integrity="sha384-WeURKcdkISkUjHZ77+LXthyEWYJMabWHrb2WEz925E9WM6I+yjuZRvUu5l21xbGT" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js" integrity="sha384-mOGjUrCoMJ8/pGqc8SQHuJdYPrdB9cjSkiuLQbw6D7orbJyMkk6xYDlYtkEH051d" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js" integrity="sha384-0RqPR2pS8PZtdf2dUWHpI+Um4DsvovzRAZPEZg7lXp4/HhxUBq0Y8VPYBE902L4u" crossorigin="anonymous"></script>
<script src="{{ URL::asset('build/libs/dropzone/dropzone-min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/filepond/filepond.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/filepond-plugin-image-preview/filepond-plugin-image-preview.min.js') }}">
    </script>
    <script
        src="{{ URL::asset('build/libs/filepond-plugin-file-validate-size/filepond-plugin-file-validate-size.min.js') }}">
    </script>
    <script
        src="{{ URL::asset('build/libs/filepond-plugin-image-exif-orientation/filepond-plugin-image-exif-orientation.min.js') }}">
    </script>
    <script src="{{ URL::asset('build/libs/filepond-plugin-file-encode/filepond-plugin-file-encode.min.js') }}"></script>

    <script src="{{ URL::asset('build/js/pages/form-file-upload.init.js') }}"></script>
   
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha384-1H217gwSVyLSIfaLxHbE7dRb3v4mYCKbpQvzx0cegeju1MVsGrX5xXxAvs/HgeFs" crossorigin="anonymous"></script>
<script>
$(document).ready(function(){
    $('#modal1').click(function() {
        var password = {{ (int) $id }};
     $('#product_id').val(password)
       
});
});
</script>
<script>
    $(document).ready(function(){
        $('#modal2').click(function() {
            var password = {{ (int) $id }};
         $('#product_id1').val(password)
           
    });
    });
    </script>
    <script>
        $(document).ready(function(){
            $('#modal3').click(function() {
                var password = {{ (int) $id }};
             $('#product_id2').val(password)
               
        });
        });
        </script>
        <script>
            $(document).ready(function(){
                $('#modal4').click(function() {
                    var password = {{ (int) $id }};
                 $('#product_id3').val(password)
                   
            });
            });
            </script>
@endsection
