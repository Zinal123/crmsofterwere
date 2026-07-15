@extends('layouts.master')
@section('title') @lang('translation.details') @endsection
@section('css')
<style>

   .table1{
    margin-bottom: 0rem !important;
    padding: 0rem !important;
   }
</style>
<script src="http://code.jquery.com/jquery-1.11.0.min.js" integrity="sha384-/Gm+ur33q/W+9ANGYwB2Q4V0ZWApToOzRuA8md/1p9xMMxpqnlguMvk8QuEFWA1B" crossorigin="anonymous"></script>
@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') invoices @endslot
@slot('title') Invoice Details @endslot
@endcomponent
<?php
$number = $totalamountwithtax;
   $no = floor($number);
   $point = round($number - $no, 2) * 100;
   $hundred = null;
   $digits_1 = strlen($no);
   $i = 0;
   $str = array();
   $words = array('0' => '', '1' => 'one', '2' => 'two',
    '3' => 'three', '4' => 'four', '5' => 'five', '6' => 'six',
    '7' => 'seven', '8' => 'eight', '9' => 'nine',
    '10' => 'ten', '11' => 'eleven', '12' => 'twelve',
    '13' => 'thirteen', '14' => 'fourteen',
    '15' => 'fifteen', '16' => 'sixteen', '17' => 'seventeen',
    '18' => 'eighteen', '19' =>'nineteen', '20' => 'twenty',
    '30' => 'thirty', '40' => 'forty', '50' => 'fifty',
    '60' => 'sixty', '70' => 'seventy',
    '80' => 'eighty', '90' => 'ninety');
   $digits = array('', 'hundred', 'thousand', 'lakh', 'crore');
   while ($i < $digits_1) {
     $divider = ($i == 2) ? 10 : 100;
     $number = floor($no % $divider);
     $no = floor($no / $divider);
     $i += ($divider == 10) ? 1 : 2;
     if ($number) {
        $plural = (($counter = count($str)) && $number > 9) ? 's' : null;
        $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
        $str [] = ($number < 21) ? $words[$number] .
            " " . $digits[$counter] . $plural . " " . $hundred
            :
            $words[floor($number / 10) * 10]
            . " " . $words[$number % 10] . " "
            . $digits[$counter] . $plural . " " . $hundred;
     } else {
        $str[] = null;
     }
  }
  $str = array_reverse($str);
  $result = implode('', $str);
  $points = ($point) ?
    "." . $words[$point / 10] . " " .
          $words[$point = $point % 10] : '';
 
  ?>
<div class = "container-fulid">
    <div class="row">
    <div class="col-xxl-12">
        <div class=" html-content" id="demo">
        <div class="row" style ="border:1px solid black;">
        <div class="col-4">
        <img src="{{ URL::asset('build/images/logo.png') }}"  alt="logo dark" style = "width:190px;"><br>
       </div>
       <div class = "col-8" style="padding-top: 21px;">
        <h2 style="font-size: 3.625rem !important;
        margin-left: -139px;">ORACLE MACHINE TECH</h2>
        <p style="text-align: center;
        margin-right: 408px;
        line-height: 27px;"> 812  ,Wagodiya GIDC SBI road First Gate Vadodara, Gujarat  391760.<br>
            Mobile No : 91 - 7096487806,
            Email  :info@oraclemachinetech.com.<br>
            GSTIN: 24AAIFO7039H1Z5,
            PAN No: AAIFO7039H. </p>
       </div>
        </div>
        <div class="row" style = "border:1px solid black";>
            @foreach($invoice as $item)
                <div class="col-6"  style="border-left:1px solid;border-right:1px solid;">
                    <div class="row" style = "margin-top: 4px;margin-bottom: 5px;">
                        <div class="col-6" >
                            
                            <div style="font-size:16px;">Invoice No :&nbsp;&nbsp;&nbsp;{{$item->invoice_id}}</div>
                            
                            
                        </div>
                        <div class="col-6" style="text-align: right;">
                       
                        <div style="font-size:16px;">Invoice Date :&nbsp;&nbsp;&nbsp;{{ date('d-M-y', strtotime($item->date)) }}</div>
                        
                        
                           
                        </div>
                    </div>
                    </div>
                    <div class="col-6"  style="border-right:1px solid;">
                        <div class="row">
                            <div class="col-6" >
                            
                                <div style="font-size:16px;">Challan No :&nbsp;&nbsp;&nbsp;{{$item->invoice_id}}</div>
                                
                                
                            </div>
                            <div class="col-6" style="text-align: right;">
                           
                            <div  style="font-size:16px;">Challan Date :&nbsp;&nbsp;&nbsp;{{ date('d-M-y', strtotime($item->date)) }}</div>
                            
                            
                               
                            </div>
                            
                        </div>
                        </div>
                </div>
                @endforeach
                <div class="row" style = "border:1px solid black";>
                    @foreach($invoice as $item)
                        <div class="col-6"  style="border-left:1px solid;border-right:1px solid;">
                            <div class="row" style = "padding-bottom: 13px;">
                                <div class="col-6">
                                    
                                    <div>P.O NO:&nbsp;&nbsp;&nbsp;{{$item->invoice_id}}</div>
                                    
                                    
                                </div>
                                <div class="col-6" style="text-align: right;">
                               
                                <div>Date :&nbsp;&nbsp;&nbsp;{{ date('d-M-y', strtotime($item->date)) }}</div>
                                
                                
                                   
                                </div>
                            </div>
                            
                            <div class="row" style = "padding-bottom: 13px;">
                                
                                <div class="col-6">
                               
                                    <div>Eway Bill Date:&nbsp;&nbsp;&nbsp;{{ date('d-M-y', strtotime($item->ewaybilldate)) }}</div>
                                    
                                    
                                       
                                    </div>
                            </div>
                            <div class="row" style = "padding-bottom: 13px;">
                                    
                                <div class="col-6">
                               
                                    <div>Transport Vehicle No:&nbsp;&nbsp;&nbsp;{{ date('d-M-y', strtotime($item->date)) }}</div>
                                    
                                    
                                       
                                    </div>
                                
                            </div>
                            </div>
                          
                            <div class="col-6"  style="border-right:1px solid;">
                                
                                <div class="row" style = "padding-bottom: 13px;">
                                    <div class="col-6" >
                                        <div>Place Of Supply:&nbsp;&nbsp;&nbsp;{{$item->placesupply}}</div>
                                     </div>
                                 </div>
                                <div class="row" style = "padding-bottom: 13px;">
                                    <div class="col-6">
                                      <div>Despatch-Through:&nbsp;&nbsp;&nbsp;{{ date('d-M-y', strtotime($item->despatchthrough)) }}</div>
                                    </div>
                                </div>
                                <div class="row" style = "padding-bottom: 13px;">
                                    <div class="col-6" >
                                    <div>Pay-Team:&nbsp;&nbsp;&nbsp;{{$item->paycondition}}</div>
                                    </div>
                                    
                                    
                                </div>
                               
                                </div>
                        </div>
                 @endforeach
            
                </div>
                </div>
              
                
</div>
           
            
            @foreach($customer as $item1)
            <div class="row"style="border-left: 1px solid black;border-right: 1px solid black;border-bottom: 1px solid black;">
                <div class="col-6">
                   
                    <div class="row" >
                        <div style="border-bottom: 1px solid black;border-right: 1px solid black;font-weight: bolder;background-color :#e96c0e;"><span style = "color:#fff; font-size: 20px;">Billed to</span></div>
                        <div class="col-3" >
                            <div style = "font-size: 13px;">Name</div>
                            <div style = "font-size: 13px;">Address</div>
                            <div style = "font-size: 13px;">Phone</div>
                            <div style = "font-size: 13px;">State</div>
                            <div style = "font-size: 13px;">GST</div>
                            <div style = "font-size: 13px;">PAN</div>

                    </div>
                    <div class="col-9" style="border-right:1px solid;">
                        <div style  = "text-transform: capitalize;font-size: 13px;"> : {{$item1->name}}</div>
                        <div style  = "text-transform: capitalize;font-size: 13px;"> : {{$item1->address}}</div>
                        <div style  = "text-transform: capitalize;font-size: 13px;"> : {{$item1->phone}}</div>
                        <div style  = "text-transform: capitalize;font-size: 13px;"> : {{$item1->state}}</div>
                        <div style  = "font-size: 13px;">: {{$item1->billinggst}}</div>
                        <div style  = "font-size: 13px;">: {{$item1->billingpan}}</div>
                        
                        
                    </div>
                </div>
                </div>

                <div class="col-6">
                
                    <div class="row">
                        <div style="border-bottom: 1px solid black;font-weight: bolder;background-color :#e96c0e;"><span style = "color:#fff; font-size: 20px;">Ship to</span></div>
                        <div class="col-3">
                        <div style = "font-size: 13px;">Name</div>
                        <div style = "font-size: 13px;">Address</div>
                        <div style = "font-size: 13px;">Phone</div>
                        <div style = "font-size: 13px;">State</div>
                        <div style = "font-size: 13px;">GST</div>
                        <div style = "font-size: 13px;">PAN</div>
                        
                    </div>
                    <div class="col-9">
                    <div style  = "text-transform: capitalize;font-size: 13px;"> : {{$item1->sname}}</div>
                        <div style  = "text-transform: capitalize;font-size: 13px;"> : {{$item1->saddress}}</div>
                        <div style  = "text-transform: capitalize;font-size: 13px;"> : {{$item1->sphone}}</div>
                        <div style  = "text-transform: capitalize;font-size: 13px;"> : {{$item1->sstate}}</div>
                        <div style  = "text-transform: capitalize;font-size: 13px;"> : {{$item1->shippinggst}}</div>
                        <div style  = "text-transform: capitalize;font-size: 13px;"> : {{$item1->shippingpan}}</div>
                       
                    </div>
                </div>
                </div>
            </div>
            @endforeach
            <div class="row">
                <table class="table1 table-bordered p-0"  style="border-right:1px solid;border-left:1px solid;border-bottom:1px solid;">
                    <thead class="" style="text-align: center;background-color :#e96c0e;color:#ffff;">
                      <tr style="vertical-align: middle;">
                        <th scope="col" rowspan="2">Name of Product</th>
                        <th scope="col" rowspan="2">HSN/SAC</th>
                        <th scope="col" rowspan="2">QTY</th>
                        <th scope="col" rowspan="2">Unit</th>
                        <th scope="col" rowspan="2">Rate</th>
                        <th scope="col" style = "border-right:1px solid;">Total</th>
                        
                        
                      </tr>
                     
                    </thead>
                    <tbody style= "text-align:center">
                        <?php $i = 1; ?>
                        @foreach($invoiceproduct as $item3)
                      <tr>
                        <td>{{$i++}}</td>
                        <td style ="text-transform: capitalize;text-align:center">{{$item3->product}}</td>
                        <td>{{$item3->hsn}}</td>
                        <td>{{$item3->quantity}}</td>
                        <td>{{$item3->unit}}</td>
                        <td>{{$item3->rate}}</td>
                        <td>{{$item3->total}}</td>
                       
                       
                      </tr>
                   @endforeach
                    </tbody>
                  
                  </table>
            </div>
            <div class="row">
                <div class="col-7" style="border-right:1px solid;border-left:1px solid;">
                    <div class="text-center">
                       <strong>Total Proforma invoice Amount in words</strong>
                        <p class="mt-2" style  = "text-transform: capitalize;"><?php echo  $result ;?></p>
                    </div>
                    <div class="row" style="border-top:1px solid;border-bottom:1px solid">
                        <p class="text-center fw-bold">Bank Details</p>
    @foreach($invoice as $item4)
<div class="col-6 text-start">
    <div>Account Holder Name :</div>
    <div>bank Account Number :</div>
    <div>Bank iFSC Code :</div>
    <div>Bank Name :</div>
    <div>Bank Branch  Name :</div>
    <div>UPI-ID</div>
</div>
<div class="col-6">
    <div>{{$item4->accountholder}}</div>
    <div>{{$item4->bankaccountnumber}}</div>
    <div>{{$item4->bankifsccode}}</div>
    <div>{{$item4->bankname}}</div>
    <div>{{$item4->bankbranchname}}</div>
    <div>oraclemachinetech.7096487806.ibz@icici</div>
</div>
                    </div>
                </div>
                <div class="col-5" style="border-right: 1px solid black;">
<div class="row">
                    <div class="col-6 g-0" style="border-bottom: 1px solid black;">
                        <div class="" style="border-bottom: 1px solid black;">Amount with Tax :</div>
                        <?php if($state == "Gujarat"){?>
                            <div  style="border-bottom: 1px solid black;">IGST (18%)</div>
                        <?php }else{?>
                            <div  style="border-bottom: 1px solid black;">CGST (9%) {{$sgstamount}}</div>
                            <div  style="border-bottom: 1px solid black;">SGST (9%) {{$cgstamount}}</div>
                       <?php }?>
                    </div>
                    <div class="col-6 text-end g-0" style="border-left: 1px solid black;border-bottom: 1px solid black;">
                        <div  style="border-bottom: 1px solid black;">{{$item4->amount}}</div>
                        <?php if($state == "Gujarat") { ?>
                            <div class=""  style="border-bottom: 1px solid black;">{{$item4->amountwithtax}}</div>
                        <?php}else{?>
                                <div style="border-bottom: 1px solid black;">{{$sgstamount}}</div>
                                <div style="border-bottom: 1px solid black;">{{$cgstamount}}</div>
                        <?php } ?>
                       
                       
                        
                    </div>
                </div>
                </div>
            </div>
            <div class="row" style="border-bottom: 1px solid black;border-left: 1px solid black;" >
                
                @endforeach
                
                <div class = "col-6" style="border-left: 1px solid black ;border-right: 1px solid black;border-top: 1px solid black">
                  <div><strong>Team And Conditation</strong></div>
                    <p>
                        (1)Payment must be made within due date of this invoice failling which interest will be charged 24% per annum from the date of supply of materials.<br>
                        (2) Our responsibility ceases on delivery of goods to carriers or rail or transport.<br>
                        (3) Goods once sold can not be taken back.<br>
                        (4) All disputes are subject to Vadodara jurisdiction only.
                    </p>
                </div>
                <div class="col-3 text-center" style="border-left: 1px solid black ;border-right: 1px solid black;border-top: 1px solid black">
                  
                    <p style="margin-top: 168px;">Receiver's Signature/Mobile No</p>
                </div>
                <div class="col-3 text-center" style="border-left: 1px solid black ;border-right: 1px solid black;border-top: 1px solid black">
                    <div> <strong> For, Oracal Machine Tech</strong> </div>
                      <p style="margin-top: 150px;">Authorised Signatory</p>
                  </div>
            </div>
            <!--end row-->
        </div>
        <!--end card-->
    </div>
    <!--end col-->
</div>
<!--end row-->
</div>
@endsection
@section('script')
<script src="{{ URL::asset('build/js/pages/invoicedetails.js') }}"></script>
<script src="{{ URL::asset('build/js/app.js') }}"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.5.3/jspdf.min.js" integrity="sha384-l7aOEgTYxgJ0nn2MziQWZCvuvJ2PtNcP05R4QwEoHW+kIS1gFpzupcQ7WhAdRKuq" crossorigin="anonymous"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js" integrity="sha384-ZZ1pncU3bQe8y31yfZdMFdSpttDoPmOZg2wguVK9almUodir1PghgT0eY7Mrty8H" crossorigin="anonymous"></script>

@endsection
