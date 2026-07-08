@extends('layouts.master')
@section('title')
@lang('Qutation')
@endsection
@section('css')
<link href="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
<!--datatable css-->
<link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" integrity="sha384-Dv1j0mqPOKbG6R+/4/adHCn5JaMBLG3iu8uTXFBM2MjEZuKwtsyLedRcRMR0cq7P" crossorigin="anonymous" rel="stylesheet" type="text/css">
<!--datatable responsive css-->
<link href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" integrity="sha384-yeqtDRnzRLecfhe1TzrbgNOTB74vG/UQ0vsFfHPCXe5rnFfjTj3GIfBHCMlUGCh5" crossorigin="anonymous" rel="stylesheet"type="text/css">
<link href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css" integrity="sha384-/m1O+MBcLoqe7amEwkvENLD+f6ePq8wBTHBWz+Kf1zp90kmGlto3IFhhNa9fZllu" crossorigin="anonymous" rel="stylesheet" type="text/css">
<link href="{{ URL::asset('build/libs/dropzone/dropzone.css') }}" rel="stylesheet">
<link rel="stylesheet" href="{{ URL::asset('build/libs/filepond/filepond.min.css') }}" type="text/css" >
<link rel="stylesheet"href="{{ URL::asset('build/libs/filepond-plugin-image-preview/filepond-plugin-image-preview.min.css') }}">
<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1')
Qutation
@endslot
@slot('title')

@endslot
@endcomponent
<?php
  $product = 1;
  $softeredetails = App\Models\Softerwere::where('product_id' ,$product)->orderBy('id' ,'desc')->get();
  $lasercutting = App\Models\Lasercutting::where('product_id' ,1)->orderBy('id' ,'desc')->get('modal');
  $fource = App\Models\Fource::where('product_id' ,$product)->orderBy('id' ,'desc')->get('modal');
  $power= App\Models\Power::where('product_id' ,$product)->orderBy('id' ,'desc')->get();
  $cutting= App\Models\Cutting::where('product_id' ,$product)->orderBy('id' ,'desc')->get();
  $cnsthinks = App\Models\Cnsthinks::where('product_id' ,$product)->orderBy('id' ,'desc')->get();
  $motor =App\Models\Motor::where('product_id' ,$product)->orderBy('id' ,'desc')->get();
  $gear = App\Models\Gear::where('product_id' ,$product)->orderBy('id' ,'desc')->get();
  $rack = App\Models\Rack::where('product_id' ,$product)->orderBy('id' ,'desc')->get();
  $softere = App\Models\Softerwere1::where('product_id' ,$product)->orderBy('id' ,'desc')->get();

?>
<div class="row">
    <div class="col-lg-12">
        <div class="card" id="invoiceList">
            <div class="card-header border-0">
                <div class="d-flex align-items-center">
                    <h5 class="card-title mb-0 flex-grow-1">Qutation</h5>
                    
                </div>
            </div>
            <div class="card-body bg-light-subtle border border-dashed border-start-0 border-end-0">
                <form action="{{route('generatequtationstore')}}" method="POST">
                    @csrf
                    <div class ="row">
                      <input type="hidden" class="form-control"placeholder="name@example.com"  name="product_id"/>
                        <div class="col-md-3">
                        <label class="col-form-label" for="basic-default-name">Quotation For</label><br>
                        <input type="radio" id="html" name="fav_language" value="With Canopy">
                        <label class="col-form-label" for="basic-default-name">With Canopy</label>
                        </div>
                        <div class="col-md-3">
                        <label class="col-form-label" style="visibility: hidden;" for="basic-default-name">Quotation For</label><br>
                        <input type="radio" id="html-2" name="fav_language" value="Without Canopy">
                        <label class="col-form-label" for="basic-default-name">Without Canopy</label>
                        </div>
                      
                   
                        
                        </div>
                        <br>
                        <div class="row">
                            <div class="col-md-4">
                            <label class="col-form-label" for="basic-default-name">Client Name</label><br>
                            <input type="text" class="form-control"placeholder="name@example.com" name="clientname"/>
                           </div>
                           <div class="col-md-4">
                            <label class="col-form-label" for="basic-default-name">Company Name</label><br>
                            <input type="text" class="form-control"placeholder="name@example.com" name="companyname"/>
                           </div>
                           <div class="col-md-4">
                            <label class="col-form-label" for="basic-default-name">GST No.</label><br>
                            <input type="text" class="form-control"placeholder="name@example.com" name="gstno"/>
                           </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                            <label class="col-form-label" for="basic-default-name">Company Address</label><br>
                            <input type="text" class="form-control"placeholder="name@example.com" name="companyaddress"/>
                           </div>
                           <div class="col-md-4">
                            <label class="col-form-label" for="basic-default-name">Select Bank</label><br>
                            <input type="text" class="form-control" name="bank" value = "1" readonly/>
        
                           </div>
                           <div class="col-md-4">
                            <label class="col-form-label" for="basic-default-name">Email Id</label><br>
                            <input type="email" class="form-control" name="email"/>
                           </div>
                          
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                            <label class="col-form-label" for="basic-default-name">WhatsApp Number</label><br>
                            <input type="number" class="form-control"  name="phone"/>
                           </div>
                           <div class="col-md-4">
                            <label class="col-form-label" for="basic-default-name">Date</label><br>
                            <input type="date" class="form-control" name ="date"/>
                           </div>
                           <div class="col-md-4">
                            <label class="col-form-label" for="basic-default-name">Reminder Date</label><br>
                            <input type="date" class="form-control"  name ="reminderdate"/>
                           </div>
                          
                        </div>
                        <br>
                        <h5 class="mb-0">Standard Configuration</h5>
                        <table class="table">
                    <thead>
                      <tr>
                        <th>SR.No</th>
                        <th>Make and Modal</th>
                        <th>Select</th>
                        
                      </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                      <tr>
                        <td>1</td>
                        <td id="qutation-select-label-1">Software Details</td>
                        <td> <select class="form-select" aria-labelledby="qutation-select-label-1" name="softweredetails">
                         <option value ="0" selected>Open this select menu</option>
                         @foreach($softeredetails as $item)
                         <option value = "{{$item->id}}">{{$item->modal}}</option>
                          @endforeach

                        </select></td>
                     </tr>
                     <tr>
                        <td>2</td>
                        <td id="qutation-select-label-2">Laser Cutting Machine</td>
                        <td>  <select class="form-select" aria-labelledby="qutation-select-label-2" name="lasercutting">
                        <option value ="0">Open this select menu</option>
                          @foreach($softeredetails as $item1)
                         <option value = "{{$item1->id}}">{{$item1->modal}}</option>
                          @endforeach
                        </select></td>
                     </tr>
                     <tr>
                        <td>3</td>
                        <td id="qutation-select-label-3">Focusing Laser Cutting Head</td>
                        <td>  <select class="form-select"aria-labelledby="qutation-select-label-3" name="focus">
                          <option  value ="0">Open this select menu</option>
                          @foreach($fource as $item)
                         <option value = "{{$item->id}}">{{$item->modal}}</option>
                          @endforeach
                        </select></td>
                     </tr>
                     <tr>
                        <td>4</td>
                        <td id="qutation-select-label-4">Power Source</td>
                        <td>  <select class="form-select"aria-labelledby="qutation-select-label-4" name="power">
                        <option  value ="0">Open this select menu</option>
                          @foreach($power as $item)
                         <option value = "{{$item->id}}">{{$item->modal}}</option>
                          @endforeach
                        </select></td>
                     </tr>
                    </tbody>
                  </table>
                  <br>
                  <h5 class="mb-0">Standard Technical Parameters</h5>
                  <table class="table">
                    <thead>
                      <tr>
                        <th>SR.No</th>
                        <th>Make and Modal</th>
                        <th>Select</th>
                        
                      </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                      <tr>
                        <td>1</td>
                        <td>Input Power</td>
                        <td><input type="text" class="form-control" id="basic-default-name" placeholder="John Doe" name ="inputpower"/></td>
                     </tr>
                     <tr>
                        <td>2</td>
                        <td id="qutation-select-label-5">Cutting Way</td>
                        <td>  <select class="form-select"aria-labelledby="qutation-select-label-5" name="cuttingway">
                        <option  value ="0">Open this select menu</option>
                          @foreach($cutting as $item)
                          <option value="{{$item->id}}">{{$item->cuttingway}}</option>
                          @endforeach
                        </select></td>
                     </tr>
                     <tr>
                        <td>3</td>
                        <td>CNC Span (mm)</td>
                        <td><input type="text" class="form-control" id="basic-default-name-2" placeholder="John Doe" name="cncspan" /></td>
                     </tr>
                     <tr>
                        <td>4</td>
                        <td>CNC Length (mm)</td>
                        <td><input type="text" class="form-control" id="basic-default-name-3" placeholder="John Doe"  name="cnslenght"/></td>
                     </tr>
                     <tr>
                        <td>5</td>
                        <td>Effective Cutting Range</td>
                        <td><input type="text" class="form-control" id="basic-default-name-4" placeholder="John Doe"  name="cuttingrang"/></td>
                     </tr>
                     <tr>
                        <td>6</td>
                        <td>Cutting Head Lifting Height (mm)</td>
                        <td><input type="text" class="form-control" id="basic-default-name-5"  value ="200" name="liftingheight" /></td>
                     </tr>
                     <tr>
                        <td>7</td>
                        <td>Laser Cutting Head Quantity</td>
                        <td><input type="text" class="form-control" id="basic-default-name-6"  value ="200"  name="headquantity"/></td>
                     </tr>
                     <tr>
                        <td>8</td>
                        <td id="qutation-select-label-6">Cutting Thickess (mm)</td>
                        <td><select class="form-select"aria-labelledby="qutation-select-label-6" name="cuttingthickess">
                          <option value="0">Open this select menu</option>
                          @foreach($cnsthinks as $item)
                          <option value="{{$item->id}}">{{$item->cuttingthinks}}</option>
                          @endforeach
                        </select></td>
                     </tr>
                     <tr>
                        <td>9</td>
                        <td>Idle Stroke Speed</td>
                        <td><input type="text" class="form-control" id="basic-default-name-7" name="strokespeed" /></td>
                     </tr>
                     <tr>
                        <td>10</td>
                        <td>Cutting Speed</td>
                        <td><input type="text" class="form-control" id="basic-default-name-8"  name="cuttingspeed" /></td>
                     </tr>
                     <tr>
                        <td>11</td>
                        <td>Drive</td>
                        <td><input type="text" class="form-control" id="basic-default-name-9"  name="drive" /></td>
                     </tr>
                    </tbody>
                  </table>
                  <br>
                  <h5 class="mb-0">Standard Configuration List</h5>
                  <table class="table">
                    <thead>
                      <tr>
                        <th>SR.No</th>
                        <th>Accessory</th>
                        <th>Select</th>
                        
                      </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                      <tr>
                        <td>1</td>
                        <td id="qutation-select-label-7">Motor</td>
                        <td><select class="form-select"aria-labelledby="qutation-select-label-7" name="motor">
                        <option value="0">Open this select menu</option>
                          @foreach($motor as $item)
                          <option value="{{$item->id}}">{{$item->companyname}}</option>
                          @endforeach
                        </select></td>
                     </tr>
                     <tr>
                        <td>2</td>
                        <td id="qutation-select-label-8">Motor Type</td>
                        <td><select class="form-select"aria-labelledby="qutation-select-label-8" name="motortype">
                        <option value="0">Open this select menu</option>
                          @foreach($motor as $item)
                          <option value="{{$item->id}}">{{$item->companyname}}</option>
                          @endforeach
                        </select></td>
                     </tr>
                     <tr>
                        <td>3</td>
                        <td id="qutation-select-label-9">Gear Box</td>
                        <td><select class="form-select"aria-labelledby="qutation-select-label-9" name="gearbox">
                        <option value="0">Open this select menu</option>
                          @foreach($gear as $item)
                          <option value="{{$item->id}}">{{$item->companyname}}</option>
                          @endforeach
                        </select></td>
                     </tr>
                     <tr>
                        <td>4</td>
                        <td id="qutation-select-label-10">Rack</td>
                        <td><select class="form-select"aria-labelledby="qutation-select-label-10" name="rack">
                        <option value="0">Open this select menu</option>
                          @foreach($rack as $item)
                          <option value="{{$item->id}}">{{$item->companyname}}</option>
                          @endforeach
                        </select></td>
                     </tr>
                     <tr>
                        <td>5</td>
                        <td id="qutation-select-label-11">Software</td>
                        <td><select class="form-select"aria-labelledby="qutation-select-label-11" name="software">
                        <option value="0">Open this select menu</option>
                          @foreach($softere as $item)
                          <option value="{{$item->id}}">{{$item->companyname}}</option>
                          @endforeach
                        </select></td>
                     </tr>
                    
                    </tbody>
                    
                  </table>
                  <br>
                  <h5 class="mb-0">Pricing</h5>
                  <table class="table">
                    <thead>
                      <tr>
                        <th>SR.No</th>
                        <th>Description</th>
                        <th>Amount</th>
                        
                      </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                      <tr>
                        <td>1</td>
                        <td><input type="text" class="form-control" name="description" ></td>
<td>                            <input type="text" class="form-control" id="basic-default-name-10" placeholder="John Doe" name="amount" /></td>
                     </tr>
                     <tr>
                        <td>2</td>
                        <td><input type="text" class="form-control" name="description1" ></td>
                        <td><input type="text" class="form-control" id="basic-default-name-11" placeholder="John Doe"  name="amount1" /></td>
                     </tr>
                     <td>3</td>
                        <td><input type="text" class="form-control" name="description2" ></td>
                        <td><input type="text" class="form-control" id="basic-default-name-12" placeholder="John Doe" name="amount2"  /></td>
                     </tr>
                    
                    </tbody>
                  </table>
                   <div class ="row">
                    <div class="col-md-12">
                    <label class="col-sm-2 col-form-label" for="basic-default-email">Optional And Party's Scopr</label><br>
                    
                   
                   </div>
                   <div class ="row">
                    <div class="col-md-12">
                    <label class="col-sm-2 col-form-label" for="basic-default-email">Notes</label><br>
                    <textarea id="editor"></textarea>
                   
                   </div>
                   <br>
                   <div class="row justify-content-end">
                          <div class="col-sm-10">
                            <button type="submit" class="btn btn-primary" style="float: right;">Submit</button>
                          </div>
                        </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    tinymce.init({
      selector: 'textarea#editor',
    });
  </script>


@endsection
