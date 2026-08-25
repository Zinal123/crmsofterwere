<?php

namespace Database\Seeders;

use App\Models\Bank;
use App\Models\Cnsthinks;
use App\Models\Customer;
use App\Models\Cutting;
use App\Models\Fource;
use App\Models\Gear;
use App\Models\Invetry;
use App\Models\Invoice;
use App\Models\Invoiceproduct;
use App\Models\Lasercutting;
use App\Models\Motor;
use App\Models\Paidamount;
use App\Models\Power;
use App\Models\Product;
use App\Models\Quation;
use App\Models\Rack;
use App\Models\Softerwere;
use App\Models\Softerwere1;
use Illuminate\Database\Seeder;

/**
 * Realistic sample data for Oracle Machine Tech, a Gujarat-based laser
 * cutting machine manufacturer/dealer - replaces test/placeholder data
 * with domain-appropriate products, customers, and transactions.
 */
class OracleMachineTechSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedBank();
        $products = $this->seedProducts();
        $config = $this->seedMachineConfig($products);
        $this->seedCustomersAndInvoices($products);
        $this->seedQuotations($products, $config);
        $this->seedInventory($products);
    }

    private function seedBank(): void
    {
        Bank::create([
            'bankholdername' => 'Oracle Machine Tech',
            'bankaccountnumber' => '780305000477',
            'bankifsccode' => 'ICIC0007803',
            'bankbranchname' => 'Madodhar Branch',
            'bankname' => 'ICICI Bank',
        ]);
    }

    /** @return Product[] [fiberMachine, co2Machine, cuttingHead, smps, lens, nozzle] */
    private function seedProducts(): array
    {
        return [
            Product::create(['name' => 'Fiber Laser Cutting Machine 1500W', 'rate' => 850000, 'unit' => 'Nos', 'make' => 'FLC-1500']),
            Product::create(['name' => 'CO2 Laser Cutting Machine 130W', 'rate' => 425000, 'unit' => 'Nos', 'make' => 'CO2-130']),
            Product::create(['name' => 'Raytools Laser Cutting Head BM109', 'rate' => 45000, 'unit' => 'Nos', 'make' => 'BM109']),
            Product::create(['name' => 'Siemens SMPS Power Supply 6EP1333-3BA10', 'rate' => 12500, 'unit' => 'Nos', 'make' => '6EP1333-3BA10']),
            Product::create(['name' => 'Precitec ProCutter Focusing Lens', 'rate' => 8500, 'unit' => 'Nos', 'make' => 'PC-750']),
            Product::create(['name' => 'Ceramic Nozzle Ring', 'rate' => 350, 'unit' => 'Nos', 'make' => 'CNR-02']),
        ];
    }

    /**
     * Every machine-config table (software, cutting head, focus, power,
     * motor/gear/rack, cutting way/thickness, software list) for the two
     * machines - returns the first-created record of each, needed by the
     * quotations seeded afterwards.
     *
     * @return array<string, mixed>
     */
    private function seedMachineConfig(array $products): array
    {
        [$fiberMachine, $co2Machine, $cuttingHead, $smps, $lens] = $products;

        $software1 = Softerwere::create(['company' => 'Cypcut', 'product_id' => $fiberMachine->id, 'modal' => 'CypCut Pro V6.2', 'logo' => '', 'image' => '', 'description' => 'Laser cutting control software with nesting and auto-focus support.']);
        Softerwere::create(['company' => 'FSCUT', 'product_id' => $co2Machine->id, 'modal' => 'FSCUT3000E', 'logo' => '', 'image' => '', 'description' => 'CNC controller software for CO2 laser cutting systems.']);

        $laserCutting1 = Lasercutting::create(['company' => 'Raytools', 'product_id' => $cuttingHead->id, 'modal' => 'BM109', 'logo' => '', 'image' => '', 'decription' => 'Auto-focus laser cutting head, capacitive height control.']);
        Lasercutting::create(['company' => 'WSX', 'product_id' => $fiberMachine->id, 'modal' => 'NC30', 'logo' => '', 'image' => '', 'decription' => 'High-precision cutting head for fiber laser machines.']);

        $focus1 = Fource::create(['company' => 'Precitec', 'product_id' => $lens->id, 'modal' => 'ProCutter 2.0', 'logo' => '', 'image' => '', 'description' => 'Focusing lens assembly with real-time process monitoring.']);
        Fource::create(['company' => 'II-VI', 'product_id' => $fiberMachine->id, 'modal' => 'HighYAG', 'logo' => '', 'image' => '', 'description' => 'Focusing optics for high-power fiber laser cutting.']);

        $power1 = Power::create(['company' => 'IPG Photonics', 'product_id' => $fiberMachine->id, 'modal' => 'YLS-1500', 'logo' => '', 'image' => '', 'description' => '1500W fiber laser source.']);
        Power::create(['company' => 'Raycus', 'product_id' => $smps->id, 'modal' => 'RFL-1500', 'logo' => '', 'image' => '', 'description' => '1500W continuous wave fiber laser source.']);

        $motor1 = Motor::create(['product_id' => $fiberMachine->id, 'companyname' => 'Panasonic Servo Motor MHMF', 'image' => '']);
        Motor::create(['product_id' => $co2Machine->id, 'companyname' => 'Yaskawa Sigma-7 Servo Motor', 'image' => '']);
        $gear1 = Gear::create(['product_id' => $fiberMachine->id, 'companyname' => 'Yaskawa Precision Gearbox']);
        Gear::create(['product_id' => $co2Machine->id, 'companyname' => 'Apex Dynamics Gearbox']);
        $rack1 = Rack::create(['product_id' => $fiberMachine->id, 'companyname' => 'THK Precision Rack']);
        Rack::create(['product_id' => $co2Machine->id, 'companyname' => 'Yang Rack & Pinion']);

        $cuttingWay1 = Cutting::create(['product_id' => $fiberMachine->id, 'cuttingway' => 'Fiber Laser Cutting - up to 20mm Mild Steel']);
        Cutting::create(['product_id' => $co2Machine->id, 'cuttingway' => 'CO2 Laser Cutting - up to 15mm Stainless Steel']);
        $thickness1 = Cnsthinks::create(['product_id' => $fiberMachine->id, 'cuttingthinks' => '20mm Mild Steel']);
        Cnsthinks::create(['product_id' => $co2Machine->id, 'cuttingthinks' => '15mm Stainless Steel']);

        $softwareList1 = Softerwere1::create(['product_id' => $fiberMachine->id, 'companyname' => 'Cypcut Nesting Module', 'image' => '']);
        Softerwere1::create(['product_id' => $co2Machine->id, 'companyname' => 'FSCUT Nesting Module', 'image' => '']);

        return compact('software1', 'laserCutting1', 'focus1', 'power1', 'motor1', 'gear1', 'rack1', 'cuttingWay1', 'thickness1', 'softwareList1');
    }

    /** 2 Gujarat customers (SGST+CGST) + 2 other-state customers (IGST), each with an invoice. */
    private function seedCustomersAndInvoices(array $products): void
    {
        [$fiberMachine, $co2Machine, $cuttingHead, $smps, $lens, $nozzle] = $products;

        $customersData = [
            [
                'name' => 'UTKARSH MART', 'address' => 'Sai garden,shopping centre,opp G.I.D.C  Bardoli Road,Kabilpour navsari-396424',
                'phone' => '9924912879', 'state' => 'Gujarat', 'gst' => '24AENPV1210N1ZA', 'pan' => 'AENPV1210N',
                'items' => [[$fiberMachine, 1], [$cuttingHead, 2]], 'paid' => 'full',
            ],
            [
                'name' => 'Bhavya Laser Works', 'address' => 'Plot No. 45, GIDC Industrial Estate, Udhna, Surat-394210',
                'phone' => '9825012345', 'state' => 'Gujarat', 'gst' => '24AAJCB5678K1Z5', 'pan' => 'AAJCB5678K',
                'items' => [[$co2Machine, 1], [$nozzle, 10]], 'paid' => 0,
            ],
            [
                'name' => 'Precision CNC Solutions', 'address' => 'Gala No. 12, MIDC Bhosari, Pune-411026',
                'phone' => '9822098765', 'state' => 'Maharashtra', 'gst' => '27AABCP4321M1Z8', 'pan' => 'AABCP4321M',
                'items' => [[$smps, 3], [$lens, 2]], 'paid' => 45000,
            ],
            [
                'name' => 'Rajasthan Metal Works', 'address' => 'F-22, Sitapura Industrial Area, Jaipur-302022',
                'phone' => '9414056789', 'state' => 'Rajasthan', 'gst' => '08AADCR9876Q1ZK', 'pan' => 'AADCR9876Q',
                'items' => [[$nozzle, 20], [$lens, 1]], 'paid' => 0,
            ],
        ];

        foreach ($customersData as $index => $c) {
            $amount = 0;
            foreach ($c['items'] as [$product, $qty]) {
                $amount += $product->rate * $qty;
            }
            $amountWithTax = round($amount * 1.18, 2);
            // The "Paid" status badge (InvoiceController::listData()) compares
            // amount === paidamount (the pre-tax figure, not amountwithtax) - so
            // "full payment" means paidamount must equal $amount exactly.
            $paid = $c['paid'] === 'full' ? $amount : $c['paid'];
            $remaining = round($amountWithTax - $paid, 2);

            $invoice = Invoice::create([
                'invoice_id' => 'OMT/2026/' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                'date' => now()->subDays(10 - $index * 2)->format('Y-m-d'),
                'totalamountbeforetax' => $amount,
                'amount' => $amount,
                'amountwithtax' => $amountWithTax,
                'bankaccountnumber' => '780305000477',
                'bankifsccode' => 'ICIC0007803',
                'accountholder' => 'Oracle Machine Tech',
                'bankname' => 'ICICI Bank',
                'bankbranchname' => 'Madodhar Branch',
                'notes' => 'Thank you for your business.',
                'paycondition' => '50% advance, balance before dispatch',
                'duedate' => now()->addDays(20 - $index * 2)->format('Y-m-d'),
                'placesupply' => $c['state'],
                'paidamount' => $paid,
                'remaining_amount' => $remaining,
            ]);

            $customer = Customer::create([
                'invoice_id' => $invoice->id,
                'name' => $c['name'],
                'address' => $c['address'],
                'phone' => $c['phone'],
                'email' => strtolower(str_replace(' ', '', $c['name'])) . '@example.com',
                'state' => $c['state'],
                'billinggst' => $c['gst'],
                'billingpan' => $c['pan'],
                'sname' => $c['name'],
                'saddress' => $c['address'],
                'sphone' => $c['phone'],
                'sstate' => $c['state'],
                'shippinggst' => $c['gst'],
                'shippingpan' => $c['pan'],
            ]);

            foreach ($c['items'] as [$product, $qty]) {
                $lineTotal = $product->rate * $qty;
                $gstAmount = round($lineTotal * 0.18, 2);
                Invoiceproduct::create([
                    'invoice_id' => $invoice->id,
                    'product_name' => $product->id,
                    'hsn' => '8456',
                    'unit' => $product->unit,
                    'rate' => $product->rate,
                    'quantity' => $qty,
                    'total' => $lineTotal,
                    'gst' => 18,
                    'gstamount' => $gstAmount,
                    'totalamount' => $lineTotal + $gstAmount,
                ]);
            }

            if ($paid > 0) {
                Paidamount::create([
                    'invoice_id' => $invoice->id,
                    'customer_id' => $customer->id,
                    'paidAmount' => $paid,
                ]);
            }
        }
    }

    private function seedQuotations(array $products, array $config): void
    {
        [$fiberMachine, $co2Machine] = $products;
        ['software1' => $software1, 'laserCutting1' => $laserCutting1, 'focus1' => $focus1, 'power1' => $power1,
            'motor1' => $motor1, 'gear1' => $gear1, 'rack1' => $rack1, 'cuttingWay1' => $cuttingWay1,
            'thickness1' => $thickness1, 'softwareList1' => $softwareList1] = $config;

        Quation::create([
            'product_id' => $fiberMachine->id,
            'clientname' => 'Rakesh Patel',
            'companyname' => 'Patel Fabrication Works',
            'gstno' => '24AAECP1234F1Z9',
            'companyaddress' => 'Plot 18, GIDC Vapi, Valsad-396195',
            'bank' => '1',
            'email' => 'rakesh@patelfab.example.com',
            // quationform.phone is an `int` column (pre-existing schema issue - too
            // small for a real 10-digit Indian mobile number), so this is a
            // truncated placeholder, not a real contact number.
            'phone' => '998877665',
            'date' => now()->subDays(5)->format('Y-m-d'),
            'reminderdate' => now()->addDays(10)->format('Y-m-d'),
            // softweredetails/lasercutting/focus/power/cuttingway/cuttingthickess/
            // motor/gearbox/rack/software are `int` columns in this legacy table -
            // they reference the corresponding config record's id, not free text.
            'softweredetails' => $software1->id,
            'lasercutting' => $laserCutting1->id,
            'focus' => $focus1->id,
            'power' => $power1->id,
            'inputpower' => '380V 3 Phase 50Hz',
            'cuttingway' => $cuttingWay1->id,
            'cncspan' => '3000mm',
            'cnslenght' => '1500mm',
            'cuttingrang' => '3000x1500mm',
            'liftingheight' => '100mm',
            'headquantity' => '1',
            'cuttingthickess' => $thickness1->id,
            'strokespeed' => '120 m/min',
            'cuttingspeed' => '25 m/min for 3mm MS',
            'drive' => 'Rack and Pinion',
            'motor' => $motor1->id,
            'motortype' => 1,
            'gearbox' => $gear1->id,
            'rack' => $rack1->id,
            'software' => $softwareList1->id,
            'description' => 'Complete fiber laser cutting machine setup with auto-focus head and nesting software.',
            'description1' => 'Includes 1 year warranty on laser source.',
            'description2' => 'Installation and training included.',
            'amount' => 850000,
            'amount1' => 45000,
            'amount2' => 8500,
            'note' => 'Prices exclusive of GST. Valid for 15 days.',
        ]);

        Quation::create([
            'product_id' => $co2Machine->id,
            'clientname' => 'Sanjay Shah',
            'companyname' => 'Shah Metal Industries',
            'gstno' => '24AABCS5678G1Z2',
            'companyaddress' => 'Survey No. 45, Sachin GIDC, Surat-394230',
            'bank' => '1',
            'email' => 'sanjay@shahmetal.example.com',
            'phone' => '992445566',
            'date' => now()->subDays(3)->format('Y-m-d'),
            'reminderdate' => now()->addDays(12)->format('Y-m-d'),
            'softweredetails' => $software1->id,
            'lasercutting' => $laserCutting1->id,
            'focus' => $focus1->id,
            'power' => $power1->id,
            'inputpower' => '380V 3 Phase 50Hz',
            'cuttingway' => $cuttingWay1->id,
            'cncspan' => '2500mm',
            'cnslenght' => '1300mm',
            'cuttingrang' => '2500x1300mm',
            'liftingheight' => '80mm',
            'headquantity' => '1',
            'cuttingthickess' => $thickness1->id,
            'strokespeed' => '100 m/min',
            'cuttingspeed' => '18 m/min for 3mm SS',
            'drive' => 'Rack and Pinion',
            'motor' => $motor1->id,
            'motortype' => 1,
            'gearbox' => $gear1->id,
            'rack' => $rack1->id,
            'software' => $softwareList1->id,
            'description' => 'CO2 laser cutting machine for stainless steel and mild steel fabrication.',
            'description1' => 'Includes 1 year warranty on laser tube.',
            'description2' => 'Installation and training included.',
            'amount' => 425000,
            'amount1' => 12500,
            'amount2' => 8500,
            'note' => 'Prices exclusive of GST. Valid for 15 days.',
        ]);
    }

    private function seedInventory(array $products): void
    {
        [, , $cuttingHead, $smps, $lens, $nozzle] = $products;

        Invetry::create(['product_id' => $nozzle->id, 'quantity' => 250, 'vandername' => 'Raytools India Pvt Ltd', 'rate' => 350]);
        Invetry::create(['product_id' => $lens->id, 'quantity' => 40, 'vandername' => 'Precitec India', 'rate' => 8500]);
        Invetry::create(['product_id' => $smps->id, 'quantity' => 15, 'vandername' => 'Siemens Distributor Ahmedabad', 'rate' => 12500]);
        Invetry::create(['product_id' => $cuttingHead->id, 'quantity' => 8, 'vandername' => 'Raytools India Pvt Ltd', 'rate' => 45000]);
    }
}
