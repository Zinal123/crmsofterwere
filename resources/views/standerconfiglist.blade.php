@extends('layouts.master')
@section('title')
Standard Config List
@endsection
@section('css')
<link href="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
<!--datatable css-->
<link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" integrity="sha384-Dv1j0mqPOKbG6R+/4/adHCn5JaMBLG3iu8uTXFBM2MjEZuKwtsyLedRcRMR0cq7P" crossorigin="anonymous" rel="stylesheet" type="text/css" />
<!--datatable responsive css-->
<link href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" integrity="sha384-yeqtDRnzRLecfhe1TzrbgNOTB74vG/UQ0vsFfHPCXe5rnFfjTj3GIfBHCMlUGCh5" crossorigin="anonymous" rel="stylesheet"type="text/css" />
<link href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css" integrity="sha384-/m1O+MBcLoqe7amEwkvENLD+f6ePq8wBTHBWz+Kf1zp90kmGlto3IFhhNa9fZllu" crossorigin="anonymous" rel="stylesheet" type="text/css" />
<link href="{{ URL::asset('build/libs/dropzone/dropzone.css') }}" rel="stylesheet">
<link rel="stylesheet" href="{{ URL::asset('build/libs/filepond/filepond.min.css') }}" type="text/css" />
<link rel="stylesheet"href="{{ URL::asset('build/libs/filepond-plugin-image-preview/filepond-plugin-image-preview.min.css') }}">

      
@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1')
Standard Config List
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
                    <h5 class="card-title mb-0 flex-grow-1">Standard Config List</h5>
                    
                </div>
            </div>
            <div class="card-body bg-light-subtle border border-dashed border-start-0 border-end-0">
                <div class="row">
                    <div class="col-lg-3">
                        <div class="nav nav-pills flex-column nav-pills-tab custom-verti-nav-pills text-center" role="tablist" aria-orientation="vertical">
                            <a class="nav-link active show" id="custom-v-pills-home-tab" data-bs-toggle="pill" href="#custom-v-pills-home" role="tab" aria-controls="custom-v-pills-home"
                                aria-selected="true">
                                <i class="ri-home-4-line d-block fs-20 mb-1"></i>
                                Motor Details</a>
                            <a class="nav-link" id="custom-v-pills-profile-tab" data-bs-toggle="pill" href="#custom-v-pills-profile" role="tab" aria-controls="custom-v-pills-profile"
                                aria-selected="false">
                                <i class="ri-user-2-line d-block fs-20 mb-1"></i>
                                Gear Box Details</a>
                            <a class="nav-link" id="custom-v-pills-messages-tab" data-bs-toggle="pill" href="#custom-v-pills-messages" role="tab" aria-controls="custom-v-pills-messages"
                                aria-selected="false">
                                <i class="ri-mail-line d-block fs-20 mb-1"></i>
                                Rack Details</a>
                                <a class="nav-link" id="custom-v-pills-custom-tab" data-bs-toggle="pill" href="#custom-v-pills-custom" role="tab" aria-controls="custom-v-pills-custom"
                                aria-selected="false">
                                <i class="ri-mail-line d-block fs-20 mb-1"></i>
                                Software Details</a>
                        </div>
                    </div> <!-- end col-->
                    <div class="col-lg-9">
                        <div class="tab-content text-muted mt-3 mt-lg-0">
                            <div class="tab-pane fade active show" id="custom-v-pills-home" role="tabpanel" aria-labelledby="custom-v-pills-home-tab">
                               <div class = "row">
                                <div class ="col-md-6">
                                </div>
                                <div class = "col-md-6" style = "text-align:right">
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModalgrid"ata-id = "1" id ="modal">
                                        Create Motor
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
                                        <th data-ordering="false">Motor</th>
                                        <th data-ordering="false">Image</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($Motor as $item)
                                    <tr>
                                        
                                        <td>{{$item->id}}</td>
                                        <td>{{$item->companyname}}</td>

                                        <td>{{$item->image}}</td>
                                        
                                        
                                        <td>
                                            <button type="button" class="btn btn-primary edit"  data-bs-toggle="modal" data-id="{{$item->id}}">
                                                 Edit
                                               </button>
                                               <button type="button" class="btn btn-danger" id = "delete" data-bs-toggle="modal" data-id="{{$item->id}}">
                                                 Delete
                                               </button>
                                                
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
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModalgrid1" data-id = "1" id ="modal1">
                                            Create Gear
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
                                            
                                            <th data-ordering="false">Image</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($Gear as $item2)
                                        <tr>
                                            
                                            <td>{{$item2->id}}</td>
                                            <td>{{$item2->companyname}}</td>
                                            <td>{{$item2->image}}</td>
                                            
                                           
                                                <td>
                                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-id="{{$item2->id}}">
                                                         Edit
                                                       </button>
                                                       <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-id="{{$item2->id}}">
                                                         Delete
                                                       </button>
                                                        
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
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModalgrid2" data-id = "1" id ="modal2">
                                           Create Rack Details
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
                                            
                                            <th data-ordering="false">Product</th>
                                           
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($Rack as $item3)
                                        <tr>
                                            
                                            <td>{{$item3->id}}</td>
                                            <td>{{$item3->companyname}}</td>
                                            <td>{{$item3->image}}</td>
                                            
                                            <td>
                                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-id="{{$item3->id}}">
                                                    Edit
                                                  </button>
                                                  <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-id="{{$item3->id}}">
                                                    Delete
                                                  </button>
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
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModalgrid3" data-id = "1" id ="modal3">
                                            Create Software Details
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
                                            
                                            <th data-ordering="false">Product</th>
                                           
                                            <th data-ordering="false">Image</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($Softerwere1 as $item4)
                                        <tr>
                                            
                                            <td>{{$item4->id}}</td>
                                            <td>{{$item4->companyname}}</td>
                                            <td>{{$item4->image}}</td>
                                            <td>
                                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-id="{{$item4->id}}">
                                                    Edit
                                                  </button>
                                                  <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-id="{{$item4->id}}">
                                                    Delete
                                                  </button>
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
                <form action="{{route('motorstore')}}" method="POST">
                    @csrf
                    <div class ="row">
                        <div class ="col-md-3">
                            <label for="firstName" class="form-label">Company Name</label>
                            <input type="text" class="form-control" id="firstName"  name = "companyname" placeholder="Enter Product">
                            <input type="text" class="form-control" id="product_id1"  name = "product_id" placeholder="Enter Product">
                        </div>
                       
                        <div class ="col-md-3">
                            <label for="firstName" class="form-label">Logo</label>
                            <input type="file" class="filepond filepond-input-multiple" multiple name="filepond" name = "image"
                            data-allow-reorder="true" data-max-file-size="3MB" data-max-files="3">
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
                <h5 class="modal-title" id="exampleModalgrid1Label">Add Gear</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{route('gearstore')}}" method="POST">
                    @csrf
                    <div class ="row">
                        <div class ="col-md-3">
                            <label for="firstName" class="form-label">Company Name</label>
                            <input type="text" class="form-control" id="firstName-2"  name = "companyname" placeholder="Enter Product">
                            <input type="text" class="form-control" id="product_id2"  name = "product_id" placeholder="Enter Product">
                        </div>
                       
                        <div class ="col-md-3">
                            <label for="firstName" class="form-label">Logo</label>
                            <input type="file" class="filepond filepond-input-multiple" multiple name="filepond" name = "image"
                            data-allow-reorder="true" data-max-file-size="3MB" data-max-files="3">
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
                <h5 class="modal-title" id="exampleModalgrid2Label">Rack Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{route('rackstore')}}" method="POST">
                    @csrf
                    <div class ="row">
                        <div class ="col-md-3">
                            <label for="firstName" class="form-label">Company Name</label>
                            <input type="text" class="form-control" id="firstName-3"  name = "companyname" placeholder="Enter Product">
                            <input type="text" class="form-control" id="product_id3"  name = "product_id" placeholder="Enter Product">
                        </div>
                       
                        <div class ="col-md-3">
                            <label for="firstName" class="form-label">Logo</label>
                            <input type="file" class="filepond filepond-input-multiple" multiple name="filepond" name = "image"
                            data-allow-reorder="true" data-max-file-size="3MB" data-max-files="3">
                        </div>
                        
                    </div>

                   
                       
                       
                       
                        
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
                <h5 class="modal-title" id="exampleModalgrid3Label">Add Softwere</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{route('softwarestore')}}" method="POST">
                    @csrf
                    <div class ="row">
                        <div class ="col-md-3">
                            <label for="firstName" class="form-label">Company Name</label>
                            <input type="text" class="form-control" id="firstName-4"  name = "companyname" placeholder="Enter Product">
                            <input type="text" class="form-control" id="product_id4"  name = "product_id" placeholder="Enter Product">
                        </div>
                       
                        <div class ="col-md-3">
                            <label for="firstName" class="form-label">Logo</label>
                            <input type="file" class="filepond filepond-input-multiple" multiple name="filepond" name = "image"
                            data-allow-reorder="true" data-max-file-size="3MB" data-max-files="3">
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
<div class="modal fade" id="editsofterwere" tabindex="-1" aria-labelledby="editsofterwereLabel" aria-modal="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editsofterwereLabel">Add Softwere</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{route('softwarestore')}}" method="POST">
                    @csrf
                    <div class ="row">
                        <div class ="col-md-3">
                            <label for="firstName" class="form-label">Company Name</label>
                            <input type="text" class="form-control" id="firstName-5"  name = "companyname" placeholder="Enter Product">
                            <input type="text" class="form-control" id="product_id4-2"  name = "product_id" placeholder="Enter Product">
                        </div>
                       
                        <div class ="col-md-3">
                            <label for="firstName" class="form-label">Logo</label>
                            <input type="file" class="filepond filepond-input-multiple" multiple name="filepond" name = "image"
                            data-allow-reorder="true" data-max-file-size="3MB" data-max-files="3">
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
<script src="{{ URL::asset('build/js/app.js') }}"></script>
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
        $('#modal').click(function() {
            var password = {{ (int) $id }};
         $('#product_id1').val(password)
           
    });
    });
    </script>
    <script>
        $(document).ready(function(){
            $('#modal1').click(function() {
                var password = {{ (int) $id }};
             $('#product_id2').val(password)
               
        });
        });
        </script>
        <script>
            $(document).ready(function(){
                $('#modal2').click(function() {
                    var password = {{ (int) $id }};
                 $('#product_id3').val(password)
                   
            });
            });
            </script>
            <script>
                $(document).ready(function(){
                    $('#modal3').click(function() {
                        var password = {{ (int) $id }};
                     $('#product_id4').val(password)
                       
                });
                });
                </script>
              <script>
                $(document).ready(function(){
                     $(document).on('click','.edit',function(){
                        var id = $(this).val();
                        ('#editsofterwere').modal('show');
                        $.ajax({
                           type:"GET",
                           url:"{ url('softerwere/show') }},",
                           success:function(response){
                              $('#bookId').val(response.bookdata.book_id);
                              $('#bookName').val(response.bookdata.book_name);
                              $('#bookAuthor').val(response.bookdata.book_author);
                              $('#bookStatus').val(response.bookdata.book_status);
                              $('#bookId').val(book_id);
                           }
                        });
                     });
                  });
            </script>

@endsection
