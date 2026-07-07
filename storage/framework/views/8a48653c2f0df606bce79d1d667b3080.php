<?php $__env->startSection('title'); ?> <?php echo app('translator')->get('translation.details'); ?> <?php $__env->stopSection(); ?>
<?php $__env->startSection('css'); ?>
<script src="http://code.jquery.com/jquery-1.11.0.min.js"></script>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
<?php $__env->startComponent('components.breadcrumb'); ?>
<?php $__env->slot('li_1'); ?> invoices <?php $__env->endSlot(); ?>
<?php $__env->slot('title'); ?> Invoice Details <?php $__env->endSlot(); ?>
<?php echo $__env->renderComponent(); ?>
<?php
$number = $amount;
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
     } else $str[] = null;
  }
  $str = array_reverse($str);
  $result = implode('', $str);
  $points = ($point) ?
    "." . $words[$point / 10] . " " . 
          $words[$point = $point % 10] : '';
 
  ?>
 
 <div class = "container-fulid">
    <div class="container mt-3">
        <div class="row justify-content-center text-center">
          <div class="col-9"><h2>Tax Invoice</h2></div>
          <div class="col-3"><span>Original For Recipient</span></div>
        </div>
        <div class="row border border-dark">
          <div class="col-6 border-end border-dark">
            <div class="row border-bottom border-dark" style="padding-top: 19px;">
              <div class="col-3">
                <img src="<?php echo e(URL::asset('build/images/logo.png')); ?>"  alt="logo dark" style = "width:100px;"><br>
              </div>
              <div class="col-9">
                <p>
                    <strong>
                     <span style="font-size: 28px">ORACAL MACHINE TECH</span></strong><br>
                    <strong>Address</strong> : 812, SBI Bank Road, 1st Gate, Whaghodia GIDC, Vadodara <br>
                    <strong>PIN NO</strong> : 391760</br>
                    <strong>Mobile-No </strong> :  91 - 7096487806,91 - 7096487807<br>
                    <strong>Email</strong> :info@oraclemachinetech.com.<br>
                    <strong>GST-NO</strong> :24AAIFO7039H1Z5,<br>
                    <strong>PAN-No</strong>: AAIFO7039H. 
                </p>
              </div>
            </div>
            <div class="row">
             <?php $__currentLoopData = $customer; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item1): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <div class="col-9">
                <span>Buyer (Bill to)</span>
                    <div style = "font-size: 13px;">Name :&nbsp;&nbsp;&nbsp; <?php echo e($item1->name); ?></div>
                    <div style = "font-size: 13px;">Address :&nbsp;&nbsp;&nbsp;<?php echo e($item1->address); ?></div>
                    <div style = "font-size: 13px;">Phone:&nbsp;&nbsp;&nbsp; <?php echo e($item1->phone); ?></div>
                    <div style = "font-size: 13px;">State: &nbsp;&nbsp;&nbsp;<?php echo e($item1->state); ?></div>
                    <div style = "font-size: 13px;">GST: &nbsp;&nbsp;&nbsp;<?php echo e($item1->billinggst); ?></div>
                    <div style = "font-size: 13px;">PAN: : &nbsp;&nbsp;&nbsp;<?php echo e($item1->billingpan); ?></div>

             
              
              </div>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
          </div>
          <?php $__currentLoopData = $invoice; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <div class="col-6">
            <div class="row border-bottom border-dark">
              <div class="col-6">
                <span>Invoice No.</span>
                <p class="fw-bold mb-0">GC-24</p>
              </div>
              <div class="col-6">
                <span>Dated</span>
                <p class="fw-bold mb-0"><?php echo e(date('d-M-y', strtotime($item->date))); ?></p>
              </div>
            </div>
            <div class="row border-bottom border-dark">
              <div class="col-6">
                <span>Pay Conditation</span>
                <p class="fw-bold mb-0"><?php echo e($item->paycondition); ?></p>
              </div>
              <div class="col-6">
                <span>Due Date</span>
                <p class="fw-bold mb-0"><?php echo e(date('d-M-y', strtotime($item->duedate))); ?></p>
              </div>
            </div>
            <div class="row border-bottom border-dark">
              <div class="col-6">
                <span>Place Of Supply</span>
                <p class="fw-bold mb-0"><?php echo e($item->placesupply); ?></p>
              </div>
              <div class="col-6">
                <span>Challan No</span>
                <p class="fw-bold mb-0"><?php echo e($item->challanno); ?></p>
              </div>
            </div>
            <div class="row border-bottom border-dark">
             <div class="col-6">
                <span>PO Number</span>
                <p class="fw-bold mb-0"><?php echo e($item->pono); ?></p>
              </div>
              <div class="col-6">
                <span>Transport Vehicle No</span>
                <p class="fw-bold mb-0"><?php echo e($item->TransportVehicleNo); ?></p>
              </div>
            </div>
            <div class="row border-bottom border-dark">
              <div class="col-6">
                <span>Despatch-Through</span>
                <p class="fw-bold mb-0"><?php echo e($item->despatchthrough); ?></p>
              </div>
              <div class="col-6">
                <span>Ewaybill-No</span>
                <p class="fw-bold"><?php echo e($item->ewaybillno); ?></p>
              </div>
              
            </div>
            <div class="row border-bottom border-dark">
              
            </div>
            <div class="row">
              <?php $__currentLoopData = $customer; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item1): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <div class="col-9">
                <span>Spplier (Ship to)</span>
                    <div style = "font-size: 13px;">Name :&nbsp;&nbsp;&nbsp; <?php echo e($item1->sname); ?></div>
                    <div style = "font-size: 13px;">Address :&nbsp;&nbsp;&nbsp;<?php echo e($item1->saddress); ?></div>
                    <div style = "font-size: 13px;">Phone:&nbsp;&nbsp;&nbsp; <?php echo e($item1->sphone); ?></div>
                    <div style = "font-size: 13px;">State: &nbsp;&nbsp;&nbsp;<?php echo e($item1->sstate); ?></div>
                    <div style = "font-size: 13px;">GST: &nbsp;&nbsp;&nbsp;<?php echo e($item1->shippinggst); ?></div>
                    <div style = "font-size: 13px;">PAN: : &nbsp;&nbsp;&nbsp;<?php echo e($item1->shippingpan); ?></div>
              </div>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
          </div>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <div class="row border border-dark">
          <div class="col-12 p-0">
            <div class="table-responsive">
              <table class="table table-bordered border-dark mb-0">
                <thead>
                  <tr>
                    <th scope="col" class="text-uppercase">Sr.No</th>
                    <th scope="col" class="text-uppercase">Product Name</th>
                    <th scope="col" class="text-uppercase text-center">HSN/SAC</th>
                    <th scope="col" class="text-uppercase text-center">Unit</th>
                    <th scope="col" class="text-uppercase text-center" style = "width: 12px;">Quantity</th>
                    <th scope="col" class="text-uppercase text-center">Rate</th>
                    <th scope="col" class="text-uppercase text-center">Amount</th>
                  </tr>
                </thead>
                <tbody class="table-group-divider">
                    <?php $i = 1; ?>
                    <?php $__currentLoopData = $invoiceproduct; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item3): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                    <th scope="row"><?php echo e($i++); ?></th>
                    <td><?php echo e($item3->product); ?></td>
                    <td class="text-center"><?php echo e($item3->hsn); ?></td>
                    <td class="text-center"><?php echo e($item3->unit); ?></td>
                    <td class="text-center"><?php echo e($item3->quantity); ?></td>
                    <td class="text-center"><?php echo e(number_format($item3->rate, 2, '.', ',')); ?></td>
                    <td class="text-center"><?php echo e(number_format($item3->total, 2, '.', ',')); ?></td>
                  </tr>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                   <tr>
                    <td scope="row" colspan="2" class="text-uppercase text-end">
                      Total
                    </td>
                   
                    <td class="text-end"></td>
                    <td class="text-end"></td>
                    <td class="text-end"></td>
                    <td class="text-end"></td>
                    <?php $__currentLoopData = $invoice; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item4): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <td class="text-center"><?php echo e(number_format($item4->amountwithtax, 2, '.', ',')); ?></td>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                  </tr>
                  <?php if($state == "Gujarat"){ ?>
                    <tr>
                      <td scope="row" colspan="2" class="text-uppercase text-end">
                        IGST(18%)
                      </td>
                      <td class="text-end"></td>
                      <td class="text-end"></td>
                      <td class="text-end"></td>
                      <td class="text-end"></td>
                      <td class="text-center"><?php echo e(number_format($igsamount, 2, '.', ',')); ?></td>
                    </tr>
                  <?php } else { ?>
                    <tr>
                      <td scope="row" colspan="2" class="text-uppercase text-end">
                        SGST(9%)
                      </td>
                      <td class="text-end"></td>
                      <td class="text-end"></td>
                      <td class="text-end"></td>
                      <td class="text-end"></td>
                      <td class="text-center"><?php echo e(number_format($sgstamount, 2, '.', ',')); ?></td>

                    </tr>
                    <tr>
                      <td scope="row" colspan="2" class="text-uppercase text-end">
                        CGST(9%)
                      </td>
                      <td class="text-end"></td>
                      <td class="text-end"></td>
                      <td class="text-end"></td>
                      <td class="text-end"></td>
                      <td class="text-center"><?php echo e(number_format($cgstamount, 2, '.', ',')); ?></td>
                      
                    </tr>
                  <?php } ?>
                  <tr>
                    <td scope="row" colspan="2" class="text-uppercase text-end">
                     Round Of Total
                    </td>
                    <td class="text-end"></td>
                    <td class="text-end"></td>
                    <td class="text-end"></td>
                    <td class="text-end"></td>
                    <td class="text-center"><?php echo e(number_format($roundof, 2, '.', ',')); ?></td>
                  
                    
                  </tr>
                  <tr>
                    <td scope="row" colspan="2" class="text-uppercase text-end">
                     Grand Total
                    </td>
                    <td class="text-end"></td>
                    <td class="text-end"></td>
                    <td class="text-end"></td>
                    <td class="text-end"></td>
                    <?php $__currentLoopData = $invoice; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item4): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <td class="text-center"><?php echo e(number_format($item4->amount, 2, '.', ',')); ?></td>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    
                  </tr>
                   
                  
                </tbody>
                
              </table>
            </div>
          </div>
        </div>

        <div class="row border border-dark">
          <div class="col-12 p-0">
            <div class="table-responsive">
              <table class="table table-bordered border-dark mb-0">
               
              
                <tfoot>
                  <tr>
                    <td colspan="5">
                      <p>Tax Amount in Words : <b><span style  = "text-transform: capitalize;"><?php echo  $result ;?></span></b></p>
                      
                    </td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>
        </div>
        <div class="row border border-dark">
          
          <div class="col-6">
            <?php $__currentLoopData = $invoice; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item4): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <p class="mb-0">Company's Bank Details</p>
            <p class="mb-0">Bank-Name : <?php echo e($item4->bankname); ?></p>
            <p class="mb-0">Account No : <?php echo e($item4->bankaccountnumber); ?></p>
            <p class="mb-0">Account Holder's Name : <?php echo e($item4->accountholder); ?></p>
            <p class="mb-0">Bank ifcode : <?php echo e($item4->bankifsccode); ?></p>
            <p class = "mb-0">UPI-ID : oraclemachinetech.7096487806.ibz@icici</p>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </div>
        </div>
        <div class="row border border-dark">
          
          <div class="col-6">
            <p class="mb-0">Team And Conditation</p>
            <p>
              (1)Payment must be made within due date of this invoice failling which interest will be charged 24% per annum from the date of supply of materials.<br>
              (2) Our responsibility ceases on delivery of goods to carriers or rail or transport.<br>
              (3) Goods once sold can not be taken back.<br>
              (4) All disputes are subject to Vadodara jurisdiction only.
          </p>
          </div>
        </div>
        <div class="row border border-dark">
          <div class="col-6 border-end border-dark">
            <span>Customer's seal & signature</span>
          </div>
          <div class="col-6 text-end">
            <span><b>ORACAL MACHINE TECH</b></span>
            <p class="mt-4">Authorised Signatory</p>
          </div>
        </div>
        <div class="row text-center">
          <span>This is a Computer Generated Invoice</span>
        </div>
      </div>
 </div>
 
   
    
  
     
        
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script'); ?>
<script src="<?php echo e(URL::asset('build/js/pages/invoicedetails.js')); ?>"></script>
<script src="<?php echo e(URL::asset('build/js/app.js')); ?>"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.5.3/jspdf.min.js"></script>
<script type="text/javascript" src="https://html2canvas.hertzen.com/dist/html2canvas.js"></script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/u411614341/domains/cms.oraclemachinetech.com/public_html/resources/views/apps-invoices-details.blade.php ENDPATH**/ ?>