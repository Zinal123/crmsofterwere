@extends('layouts.master')
@section('title') @lang('translation.create-invoice') @endsection
@section('css')
<link href="{{ URL::asset('build/libs/dropzone/dropzone.css') }}" rel="stylesheet">
<!-- Sweet Alert css-->
<link href="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet">
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js" integrity="sha384-arH1uA5PLAeRKjGnbtLWJE0jM7h24iSP2roxuBnksKET0bXe6kn6KiH9V9d6S41t" crossorigin="anonymous"></script>

@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') invoices @endslot
@slot('title')Create Invoice @endslot
@endcomponent
<x-ui.back-link :route="route('invoice')" label="Back to Invoices" />
<div class="row justify-content-center">
    <div class="col-xxl-12">
        <div class="card">
            <form class="needs-validation" novalidate id="invoice_form">
          
                <div class="card-body border-bottom border-bottom-dashed p-4">
                    <div class="row">
                        <div class="col-lg-4">
                            {{-- Not a real upload control - this file input had no onchange
                                 handler and nothing read its value; the logo shown here is
                                 always the fixed company logo, swapped by theme (dark/light)
                                 via CSS, not by this input. Simplified to a static display. --}}
                            <div class="profile-user mx-auto mb-3">
                                <span class="d-block overflow-hidden border border-dashed d-flex align-items-center justify-content-center rounded" style="height: 60px; width: 256px;">
                                    <img src="{{ URL::asset('build/images/logo.png') }}" class="card-logo card-logo-dark user-profile-image img-fluid" alt="Company logo dark theme">
                                    <img src="{{ URL::asset('build/images/header.jpeg') }}" class="card-logo card-logo-light user-profile-image img-fluid" alt="Company logo light theme">
                                </span>
                            </div>
                            <div>
                                
                                <div class="mb-2">
                                    <textarea class="form-control bg-light border-0" id="companyAddress" rows="3" placeholder="Company Address"  required readonly="readonly">12 Whaghodia GIDC 1st Gate Whaghodia Road , Vadodara</textarea>
                                    <div class="invalid-feedback">
                                        Please enter a address
                                    </div>
                                </div>
                                <div>
                                    <input type="text" class="form-control bg-light border-0" id="companyaddpostalcode" minlength="5" maxlength="6" placeholder="Enter Postal Code" required  value = "391760" readonly="readonly"/>
                                    <div class="invalid-feedback">
                                        The US zip code must contain 5 digits, Ex. 45678
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--end col-->
                        <div class="col-lg-4 ms-auto">
                           
                            <div class="mb-2">
                                <input type="email" class="form-control bg-light border-0" id="companyEmail" placeholder="Email Address" required  value = "info@oraclemachinetech.com" readonly="readonly"/>
                                <div class="invalid-feedback">
                                    Please enter a valid email, Ex., example@gamil.com
                                </div>
                            </div>
                            <div class="mb-2">
                                <input type="text" class="form-control bg-light border-0" id="companyWebsite" placeholder="Website" required value = "www.oraclemachinetech.com"  readonly="readonly"/>
                                <div class="invalid-feedback">
                                    Please enter a website, Ex., www.example.com
                                </div>
                            </div>
                            <div>
                                <input type="text" class="form-control bg-light border-0"  id="compnayContactno" placeholder="Contact No" required  value = "+91-7096487806" readonly="readonly"/>
                                <div class="invalid-feedback">
                                    Please enter a contact number
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end row-->
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-lg-3 col-sm-6">
                            <label for="invoicenoInput">Invoice No</label>
                            <input type="text" class="form-control bg-light border-0" id="invoicenoInput" placeholder="Invoice No"  readonly="readonly" />
                        </div>
                        <!--end col-->
                        <div class="col-lg-3 col-sm-6">
                            <div>
                                <label for="date-field">Invoice Date</label>
                                <input type="text" class="form-control bg-light border-0" id="date-field" data-provider="flatpickr" data-time="true" placeholder="Select Date-time">
                            </div>
                            
                        </div>
                        
                        <div class="col-lg-3 col-sm-6">
                            <div>
                                <label for="date-field">Pay Conditation</label>
                                <input type="text" class="form-control bg-light border-0" id="paycondition" placeholder="Pay Conditation" name = "paycondition"/>
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6">
                            <div>
                                <label for="date-field">Due Date</label>
                                <input type="text" class="form-control bg-light border-0" id="duedate"  name = "duedate" data-provider="flatpickr" data-time="true" placeholder="Select Due-Date" name = "duedate">
                            </div>
                        </div>
                        <!--end col-->
                       
                        <!--end col-->
                       
                        <!--end col-->
                    </div>
                    <br>
                    <div class="row g-3">
                        <div class="col-lg-3 col-sm-6">
                            <div>
                                <label for="date-field">Place Of Supply <span class="text-danger">*</span></label>
                                <input type="text" class="form-control bg-light border-0" id="placesupply"  name = "placesupply" placeholder="Enter Place Of Supply">
                            </div>
                        </div>
                      
                        <div class="col-lg-3 col-sm-6">
                            <div>
                                <label for="date-field">Challan No</label>
                                <input type="text" class="form-control bg-light border-0" id="challanno"  name = "challanno" placeholder="Enter Place Of Supply">
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6">
                            <div>
                                <label for="date-field">Challan Date</label>
                                <input type="text" class="form-control bg-light border-0" id="challandate"  name = "challandate" data-provider="flatpickr" data-time="true" placeholder="Select challandate" >
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6">
                            <div>
                                <label for="date-field">PO Number</label>
                                <input type="text" class="form-control bg-light border-0" id="ponumber"  name = "ponumber" placeholder="Enter Place Of Supply">
                                
                            </div>
                        </div>
                    </div>
                    <br>
                    <div class="row g-3">
                        <div class="col-lg-3 col-sm-6">
                            <div>
                                <label for="date-field">Transport Vehicle No</label>
                                <input type="text" class="form-control bg-light border-0" id="transportvehicle"  name = "transportvehicle" placeholder="Enter Transport Vehicle No">
                            </div>
                        </div>
                      
                        <div class="col-lg-3 col-sm-6">
                            <div>
                                <label for="date-field">Despatch-Through</label>
                                <input type="text" class="form-control bg-light border-0" id="despatchthrough"  name = "despatchthrough" placeholder="Enter despatchthrough">
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6">
                            <div>
                                <label for="date-field">Ewaybill-No</label>
                                <input type="text" class="form-control bg-light border-0" id="ewaybillno"  name = "ewaybillno" placeholder="Enter ewaybillno">
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6">
                            <div>
                                <label for="date-field">Ewaybill-Date</label>
                                <input type="text" class="form-control bg-light border-0" id="ewaybilldate"  name = "ewaybilldate" data-provider="flatpickr" data-time="true" placeholder="Enter ewaybilldate">
                            </div>
                        </div>
                    </div>
                    
                </div>
                <div class="card-body p-4 border-top border-top-dashed">
                    <div class="row">
                        <div class="col-lg-4 col-sm-6">
                            <div>
                                <label for="billingName" class="text-muted text-uppercase fw-semibold">Billing Address <span class="text-danger">*</span></label>
                            </div>
                            <div class="mb-2">
                                <label for="billingName" class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control bg-light border-0" id="billingName" placeholder="Full Name" required />
                                <div class="invalid-feedback">
                                    Please enter a full name
                                </div>
                            </div>
                            <div class="mb-2">
                                <label for="billingAddress" class="form-label">Address <span class="text-danger">*</span></label>
                                <textarea class="form-control bg-light border-0" id="billingAddress" rows="3" placeholder="Address" required></textarea>
                                <div class="invalid-feedback">
                                    Please enter a address
                                </div>
                            </div>
                            <div class="mb-2">
                                <label for="billingPhoneno" class="form-label">Phone <span class="text-danger">*</span></label>
                                <input type="text" class="form-control bg-light border-0"  id="billingPhoneno" placeholder="Phone"  minlength="10" maxlength="11" required />
                                <div class="invalid-feedback">
                                        The phone must contain 10 digits
                                    </div>
                            </div>
                            <div class="mb-3">
                                <label for="billingstate" class="form-label">State <span class="text-danger">*</span></label>
                                <select class="form-select bg-light border-0" id="billingstate" required>
                                    <option value="">Select State</option>
                                    @foreach($states as $stateOption)
                                        <option value="{{ $stateOption }}">{{ $stateOption }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">
                                    Please select a State
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="billinggst" class="form-label">GST <span class="text-danger">*</span></label>
                                <input type="text" class="form-control bg-light border-0" id="billinggst" placeholder="GST" required />
                                <div class="invalid-feedback">
                                    Please enter a GST number
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="billingpan" class="form-label">PAN Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control bg-light border-0" id="billingpan" placeholder="PAN Number" required />
                                <div class="invalid-feedback">
                                    Please enter a PAN number
                                </div>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="same" name="same" onchange="billingFunction()" />
                                <label class="form-check-label" for="same">
                                    Will your Billing and Shipping address same?
                                </label>
                            </div>
                           
                        </div>
                        <!--end col-->
                        <div class="col-sm-6 ms-auto">
                            <div class="row">
                                <div class="col-lg-8">
                                    <div>
                                        <label for="shippingName" class="text-muted text-uppercase fw-semibold">Shipping Address <span class="text-danger">*</span></label>
                                    </div>
                                    <div class="mb-2">
                                        <label for="shippingName" class="form-label">Full Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control bg-light border-0" id="shippingName" placeholder="Full Name" required />
                                        <div class="invalid-feedback">
                                            Please enter a full name
                                        </div>
                                    </div>
                                    <div class="mb-2">
                                        <label for="shippingAddress" class="form-label">Address <span class="text-danger">*</span></label>
                                        <textarea class="form-control bg-light border-0" id="shippingAddress" rows="3" placeholder="Address" required></textarea>
                                        <div class="invalid-feedback">
                                            Please enter a address
                                        </div>
                                    </div>
                                    <div class="mb-2">
                                        <label for="shippingPhoneno" class="form-label">Phone <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control bg-light border-0"  id="shippingPhoneno"  placeholder="Phone"  minlength="10" maxlength="11" required />
                                        <div class="invalid-feedback">
                                            Please enter a phone number
                                        </div>
                                    </div>
                                    <div class="mb-2">
                                        <label for="shippingstate" class="form-label">State <span class="text-danger">*</span></label>
                                        <select class="form-select bg-light border-0" id="shippingstate" required>
                                            <option value="">Select State</option>
                                            @foreach($states as $stateOption)
                                                <option value="{{ $stateOption }}">{{ $stateOption }}</option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback">
                                            Please select a State
                                        </div>
                                    </div>
                                    <div class="mb-2">
                                        <label for="shippinggst" class="form-label">GST <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control bg-light border-0" id="shippinggst" placeholder="GST" required />
                                        <div class="invalid-feedback">
                                            Please enter a GST number
                                        </div>
                                    </div>
                                    <div class="mb-2">
                                        <label for="shippingpan" class="form-label">PAN Number <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control bg-light border-0" id="shippingpan" placeholder="PAN Number" required />
                                        <div class="invalid-feedback">
                                            Please enter a PAN number
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--end col-->
                    </div>
                    <!--end row-->
                </div>
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="invoice-table table table-borderless table-nowrap mb-0" id = "table" >
                            <thead class="align-middle">
                                <tr class="table-active">
                                    <th scope="col" style="width: 50px;">#</th>
                                    <th scope="col">Product Details</th>
                                    <th scope="col">HSN</th>
                                    <th scope="col">Unit</th>
                                    <th scope="col">
                                        <div class="d-flex currency-select input-light align-items-center">
                                            Rate
                                            <select class="form-selectborder-0 bg-light" data-choices data-choices-search-false id="choices-payment-currency" onchange="otherPayment()">
                                            <option value="₹">(₹)</option>
                                            </select>
                                        </div>
                                    </th>
                                    <th scope="col">Quantity</th>
                                    <th scope="col">Total</th>
                                    <th scope="col">GST</th>
                                    <th scope="col">GST Amount</th>
                                    <th scope="col">Total Amount</th>
                                    <th scope="col" class="text-end" style="width: 105px;"></th>
                                </tr>
                            </thead>
                            <tbody id="newlink">
                                <tr id="1" class="product">
                                    <th scope="row" class="product-id">1</th>
                                    <td class="text-start">
                                        <div class="mb-2">
                                        <select class="form-select item" data-choices data-choices-sorting="true"  id = "productName-1" onchange="otherPayment1()">
                                     <option value ="0">Select product</option>
                                        @foreach($product  as $p)
                                        <option value ="{{$p->id}}">{{$p->name}}</option>
                                         @endforeach

                                   </select>
                                            
                                        </div>
                                       
                                    </td>
                                    <td>
                                        <input type="text" class="form-control bg-light border-0 hsn" id="hsn-1" step="0.01" placeholder="0.00" />
                                        
                                    </td>
                                    <td>
                                        <input type="text" class="form-control bg-light border-0 unit" id="unit-1" step="0.01" placeholder="0.00"  />
                                        
                                    </td>
                                    <td>
                                        <input type="number" class="form-control product-price bg-light border-0" id="productRate-1" step="0.01" placeholder="0.00"  />
                                        
                                    </td>
                                    <td>
                                        <div class="input-step">
                                            <button type="button" class='minus'>–</button>
                                            <input type="number" class="product-quantity" id="product-qty-1" value="0" readonly >
                                            <button type="button" class='plus'>+</button>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="number" class="form-control  bg-light border-0 product-line-price" id="productPrice-1" step="0.01" placeholder="0.00"  readonly="readonly" />
                                        
                                    </td>
                                    <td>
                                        <select class="form-select bg-light border-0 gst" id="gst-1">
                                            <option value="0">0%</option>
                                            <option value="5">5%</option>
                                            <option value="12">12%</option>
                                            <option value="18" selected>18%</option>
                                            <option value="28">28%</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control  bg-light border-0 withtax" id="withtax-1" step="0.01" placeholder="0.00"  readonly="readonly"  />
                                        
                                    </td>
                                    <td>
                                        <input type="text" class="form-control  bg-light border-0 total" id="total-1" step="0.01" placeholder="0.00"  readonly="readonly"  />
                                        
                                    </td>
                                  
                                    <td class="product-removal">
                                        <a href="javascript:void(0)" class="btn btn-danger">Delete</a>
                                    </td>
                                </tr>
                            </tbody>
                            
                            <tbody>
                                <tr id="newForm" style="display: none;">
                                    <td class="d-none" colspan="5">
                                        <p>Add New Form</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="5">
                                        <a href="javascript:new_link()" id="add-item" class="btn btn-soft-secondary fw-medium"><i class="ri-add-fill me-1 align-bottom"></i> Add Item</a>
                                    </td>
                                </tr>
                                <tr class="border-top border-top-dashed mt-2">
                                    <td colspan="2"></td>
                                    <td colspan="10" class="p-0">
                                        <table class="table table-borderless table-sm table-nowrap align-middle mb-0" >
                                            <tbody>
                                            
                                           
                                                <tr>
                                                    <th scope="row">Total Amount Before Tax</th>
                                                    <td style="width:150px;">
                                                        <input type="text" class="form-control bg-light border-0" id="cart-total" placeholder="0.00" readonly />
                                                    </td>
                                                </tr>
                                              
                                                <tr>
                                                    <th scope="row">SGST</th>
                                                    <td>
                                                        <input type="text" class="form-control bg-light border-0" id="cart-totalbeforetax" placeholder="0.00" readonly />
                                                    </td>
                                                </tr>
                                                
                                                <tr >
                                                    <th scope="row">Grand Total</th>
                                                    <td>
                                                        <input type="text" class="form-control bg-light border-0" id="cart-amount" placeholder="0.00" readonly />
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <!--end table-->
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <!--end table-->
                    </div>
                    <div class="row mt-3">
                        
                        <!--end col-->
                    </div>
                    <!--end row-->
                    <div class="row mt-3">
                        <div class="col-lg-4">
                           <h6>Bank Details</h6>
                           <br>
                            @foreach($bank as $bank)
                            <div class="mb-2">
                                <input class="form-control bg-light border-0" type="text" id="cardholderName" placeholder="Account Holder Name"   value="{{$bank->bankholdername}}">
                            </div>
                            <div class="mb-2">
                                <input class="form-control bg-light border-0" type="text" id="cardNumber" placeholder="Bank Account Number"  value="{{$bank->bankaccountnumber}}" >
                            </div>
                            <div  class="mb-2">
                                <input class="form-control  bg-light border-0" type="text" id="cardifsccode" placeholder="Bank IFSC Code" value="{{$bank->bankifsccode}}" />
                            </div>
                            <div  class="mb-2">
                                <input class="form-control  bg-light border-0" type="text" id="bankname" placeholder="Bank Name"  value="{{$bank->bankname}}" />
                            </div>
                            <div  class="mb-2">
                                <input class="form-control  bg-light border-0" type="text" id="bankbranchname" placeholder="Bank  Branch Name"  value="{{$bank->bankbranchname}}" />
                            </div>
                            @endforeach
                        </div>
                        <!--end col-->
                    </div>
                    <!--end row-->
                    <div class="mt-4">
                        <label for="exampleFormControlTextarea1" class="form-label text-muted text-uppercase fw-semibold">NOTES <span class="text-danger">*</span></label>
                       
                                         <textarea class="form-control alert alert-info" id="exampleFormControlTextarea1" placeholder="Notes" rows="2" required >1)Payment must be made within due date of this invoice failling which interest will be charged 24% per annum from the date of supply of materials
                            (2) Our responsibility ceases on delivery of goods to carriers or rail or transport.
                            (3) Goods once sold can not be taken back.
                            (4) All disputes are subject to Vadodara jurisdiction only.
                           
                        </textarea>
                        
                    </div>
                    <div class="hstack gap-2 justify-content-end d-print-none mt-4">
                        <button type="submit" class="btn btn-success"><i class="ri-printer-line align-bottom me-1"></i> Save</button>
                        
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!--end col-->
</div>
<!--end row-->
@endsection
@section('script')
<script src="{{ URL::asset('build/libs/dropzone/dropzone-min.js') }}"></script>
<script src="{{ URL::asset('build/libs/cleave.js/cleave.min.js') }}"></script>
<script src="{{ URL::asset('build/js/pages/invoicecreate.init.js') }}"></script>
<script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>


@endsection
