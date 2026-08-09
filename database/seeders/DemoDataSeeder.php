<?php

namespace Database\Seeders;

use App\Models\ChartAccount;
use App\Models\ClientAccount;
use App\Models\ClientMachine;
use App\Models\DailyTransaction;
use App\Models\Employee;
use App\Models\ExpenseCategory;
use App\Models\Invetry;
use App\Models\Machine;
use App\Models\SalaryPayment;
use App\Models\Ticket;
use App\Models\TicketProblemType;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorBill;
use App\Models\VendorPayment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Fabricated showcase data for demos and screenshots. Everything here is
 * invented - no real customers, no production figures. Safe to ship in a
 * public repo. Spread across the last six months so the dashboard trend
 * charts have something to draw.
 *
 * Run explicitly (never wired into DatabaseSeeder, so it can't touch a real
 * environment by accident):
 *   php artisan db:seed --class=Database\Seeders\DemoDataSeeder
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            ChartOfAccountsSeeder::class,
            TicketProblemTypeSeeder::class,
        ]);

        [$owner, $manager, $account, $workers] = $this->users();
        $products = $this->products();
        $this->inventory($products);
        $this->invoices($products);
        $machines = $this->machines();
        $this->jobs($machines, $owner, $workers);
        $employees = $this->employees();
        $this->payroll($employees, $owner);
        $this->vendorsAndBills($owner);
        $this->expenses($owner);
        $this->clientPortal($products);
    }

    /** One demo login per staff role. Password for all: Dashboard@123 */
    private function users(): array
    {
        $make = function (string $name, string $email, string $role) {
            $user = User::updateOrCreate(
                ['email' => $email],
                ['name' => $name, 'password' => Hash::make('Dashboard@123'), 'is_active' => true, 'email_verified_at' => now()]
            );
            $user->syncRoles([$role]);

            return $user;
        };

        $owner = $make('Owner Demo', 'owner-demo@test.local', 'Owner');
        $manager = $make('Manager Demo', 'manager-demo@test.local', 'Manager');
        $account = $make('Account Demo', 'account-demo@test.local', 'Account');
        $workers = collect([
            $make('Ravi Kumar', 'worker-demo@test.local', 'Worker'),
            $make('Anil Sharma', 'worker2-demo@test.local', 'Worker'),
        ]);

        return [$owner, $manager, $account, $workers];
    }

    private function products(): array
    {
        $catalogue = [
            ['Fiber Laser Nozzle 2.0mm', '450', 'pcs', 'Raytools'],
            ['Protective Lens 30x5', '850', 'pcs', 'WSX'],
            ['Ceramic Ring', '320', 'pcs', 'Precitec'],
            ['Focusing Lens D28 F125', '2200', 'pcs', 'Raytools'],
            ['Collimating Lens D30', '2600', 'pcs', 'WSX'],
            ['Nozzle Connector', '1500', 'pcs', 'Precitec'],
        ];

        $ids = [];
        foreach ($catalogue as [$name, $rate, $unit, $make]) {
            $ids[] = DB::table('product')->insertGetId([
                'name' => $name, 'rate' => $rate, 'unit' => $unit, 'make' => $make,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        return $ids;
    }

    private function inventory(array $productIds): void
    {
        // A couple deliberately below the default low-stock threshold so the
        // dashboard alert has something to show.
        $qtys = [40, 3, 60, 5, 25, 18];
        foreach ($productIds as $i => $pid) {
            Invetry::create([
                'product_id' => $pid,
                'quantity' => $qtys[$i] ?? 20,
                'vandername' => 'Demo Supplier',
                'rate' => '400',
            ]);
        }
    }

    private function invoices(array $productIds): void
    {
        $customers = ['Shree Engineering', 'Metro Fabricators', 'Gujarat Steel Works', 'Precision Cut Co', 'Anand Industries', 'Sunrise Metals'];

        for ($i = 0; $i < 28; $i++) {
            $date = Carbon::now()->subMonths(random_int(0, 5))->subDays(random_int(0, 27));
            $lineCount = random_int(1, 3);
            $beforeTax = 0;

            $lines = [];
            for ($l = 0; $l < $lineCount; $l++) {
                $pid = $productIds[array_rand($productIds)];
                $rate = (float) DB::table('product')->where('id', $pid)->value('rate');
                $qty = random_int(2, 15);
                $total = $rate * $qty;
                $gstAmount = $total * 0.18;
                $beforeTax += $total;
                $lines[] = compact('pid', 'rate', 'qty', 'total', 'gstAmount');
            }

            $tax = $beforeTax * 0.18;
            $withTax = $beforeTax + $tax;
            // Mix of fully paid, partially paid and unpaid for realistic AR.
            $paid = [$withTax, $withTax, round($withTax / 2, 2), 0][array_rand([0, 1, 2, 3])];
            $remaining = round($withTax - $paid, 2);

            $invoiceId = DB::table('invoice')->insertGetId([
                'invoice_id' => 'INV-' . (1000 + $i),
                'date' => $date->toDateString(),
                'placesupply' => 'Gujarat',
                'totalamountbeforetax' => (string) round($beforeTax, 2),
                'amount' => (string) round($beforeTax, 2),
                'amountwithtax' => (string) round($withTax, 2),
                'paidamount' => (string) $paid,
                'remaining_amount' => (string) $remaining,
                'notes' => 'Demo invoice',
                'created_at' => $date, 'updated_at' => $date,
            ]);

            DB::table('customer')->insert([
                'invoice_id' => $invoiceId,
                'name' => $customers[array_rand($customers)],
                'address' => 'Plot 12, GIDC, Rajkot',
                'phone' => '98' . random_int(10000000, 99999999),
                'state' => 'Gujarat',
                'created_at' => $date, 'updated_at' => $date,
            ]);

            foreach ($lines as $line) {
                DB::table('invoiceproduct')->insert([
                    'invoice_id' => $invoiceId,
                    'product_name' => $line['pid'], // this column stores the product id
                    'unit' => 'pcs',
                    'hsn' => '9013',
                    'rate' => (string) $line['rate'],
                    'quantity' => (string) $line['qty'],
                    'total' => (string) round($line['total'], 2),
                    'gst' => '18',
                    'gstamount' => (string) round($line['gstAmount'], 2),
                    'totalamount' => (string) round($line['total'] + $line['gstAmount'], 2),
                    'created_at' => $date, 'updated_at' => $date,
                ]);
            }
        }
    }

    private function machines(): array
    {
        $names = ['Fiber Cutter FC-3015', 'Tube Laser TL-6022', 'CO2 Engraver CE-1390', 'Bending Press BP-110', 'Welding Station WS-04'];
        $machines = [];
        foreach ($names as $i => $name) {
            $machines[] = Machine::create([
                'name' => $name,
                'is_active' => $i !== 4,
                'latitude' => 22.30 + ($i * 0.01),
                'longitude' => 70.80 + ($i * 0.01),
            ]);
        }

        return $machines;
    }

    private function jobs(array $machines, User $owner, $workers): void
    {
        $titles = ['Nozzle replacement', 'Lens cleaning', 'Calibration check', 'Bearing service', 'Chiller maintenance', 'Head alignment', 'Firmware update', 'Belt replacement'];

        // Completed jobs across recent weeks (some today) for throughput stats.
        for ($i = 0; $i < 12; $i++) {
            $done = Carbon::now()->subDays(random_int(0, 20));
            $this->makeJob($machines, $owner, $workers, [
                'title' => $titles[array_rand($titles)],
                'status' => 'completed',
                'due_date' => $done->copy()->subDays(2)->toDateString(),
                'completed_at' => $done,
                'completion_notes' => 'Work completed and verified.',
                'created_at' => $done->copy()->subDays(3),
            ]);
        }
        // A few completed *today* specifically.
        for ($i = 0; $i < 3; $i++) {
            $this->makeJob($machines, $owner, $workers, [
                'title' => $titles[array_rand($titles)],
                'status' => 'completed',
                'due_date' => today()->toDateString(),
                'completed_at' => now()->subHours($i + 1),
                'completion_notes' => 'Done today.',
                'created_at' => now()->subDay(),
            ]);
        }
        // Active pipeline.
        foreach (['assigned', 'assigned', 'in_progress', 'in_progress', 'on_hold'] as $status) {
            $this->makeJob($machines, $owner, $workers, [
                'title' => $titles[array_rand($titles)],
                'status' => $status,
                'due_date' => today()->addDays(random_int(1, 7))->toDateString(),
                'on_hold_reason' => $status === 'on_hold' ? 'Awaiting spare part' : null,
            ]);
        }
        // Pending approval.
        for ($i = 0; $i < 2; $i++) {
            $this->makeJob($machines, $owner, $workers, [
                'title' => $titles[array_rand($titles)],
                'status' => 'pending_approval',
                'due_date' => today()->addDays(5)->toDateString(),
            ]);
        }
        // Overdue (past due, flagged, still open).
        for ($i = 0; $i < 2; $i++) {
            $this->makeJob($machines, $owner, $workers, [
                'title' => $titles[array_rand($titles)],
                'status' => 'in_progress',
                'due_date' => today()->subDays(random_int(2, 6))->toDateString(),
                'overdue_flagged_at' => now()->subDay(),
            ]);
        }
    }

    private function makeJob(array $machines, User $owner, $workers, array $attrs): void
    {
        DB::table('jobs')->insert(array_merge([
            'machine_id' => $machines[array_rand($machines)]->id,
            'site_name' => 'Rajkot Plant',
            'priority' => ['low', 'medium', 'high', 'urgent'][array_rand([0, 1, 2, 3])],
            'created_by' => $owner->id,
            'assigned_to' => $workers->random()->id,
            'created_at' => now(),
            'updated_at' => now(),
        ], $attrs));
    }

    private function employees(): array
    {
        $data = [
            ['Suresh Yadav', 'monthly', 22000],
            ['Prakash Mehta', 'monthly', 26000],
            ['Dinesh Patel', 'daily', 700],
            ['Kiran Rao', 'daily', 650],
        ];

        $employees = [];
        foreach ($data as [$name, $payType, $rate]) {
            $employees[] = Employee::create([
                'name' => $name,
                'phone' => '97' . random_int(10000000, 99999999),
                'department' => 'Production',
                'designation' => 'Technician',
                'joining_date' => Carbon::now()->subYears(1)->toDateString(),
                'pay_type' => $payType,
                'pay_rate' => $rate,
                'overtime_rate_per_hour' => 120,
                'is_active' => true,
            ]);
        }

        return $employees;
    }

    private function payroll(array $employees, User $owner): void
    {
        // Monthly salary payment for each employee, last 4 months.
        foreach ($employees as $emp) {
            for ($m = 3; $m >= 0; $m--) {
                $date = Carbon::now()->subMonths($m)->startOfMonth()->addDays(4);
                $amount = $emp->pay_type === 'monthly' ? $emp->pay_rate : $emp->pay_rate * 26;
                SalaryPayment::create([
                    'employee_id' => $emp->id,
                    'date' => $date->toDateString(),
                    'amount' => $amount,
                    'note' => 'Monthly salary',
                    'paid_by' => $owner->id,
                ]);
            }
        }
    }

    private function vendorsAndBills(User $owner): void
    {
        $vendors = [
            ['Raytools India Pvt Ltd', 'Optics'],
            ['GIDC Power Supplies', 'Electrical'],
            ['Metro Gases', 'Consumables'],
        ];

        foreach ($vendors as [$name, $category]) {
            $vendor = Vendor::create(['name' => $name, 'category' => $category, 'is_active' => true]);

            for ($b = 0; $b < 2; $b++) {
                $date = Carbon::now()->subMonths(random_int(0, 4));
                $amount = random_int(15, 60) * 1000;
                $bill = VendorBill::create([
                    'vendor_id' => $vendor->id,
                    'bill_number' => 'BILL-' . random_int(100, 999),
                    'amount' => $amount,
                    'date' => $date->toDateString(),
                    'description' => 'Supplies',
                    'created_by' => $owner->id,
                ]);
                // Partial payment -> leaves a payable balance.
                VendorPayment::create([
                    'vendor_id' => $vendor->id,
                    'vendor_bill_id' => $bill->id,
                    'amount' => round($amount * 0.6, 2),
                    'date' => $date->copy()->addDays(5)->toDateString(),
                    'payment_mode' => 'bank',
                    'description' => 'Part payment',
                    'created_by' => $owner->id,
                ]);
            }
        }
    }

    private function expenses(User $owner): void
    {
        $categories = [
            ['Electricity', 'payment'], ['Diesel / Fuel', 'payment'], ['Rent', 'payment'],
            ['Maintenance', 'payment'], ['Office Supplies', 'payment'],
            ['Other Income', 'receipt'], ['Scrap Sale', 'receipt'],
        ];

        $catModels = [];
        foreach ($categories as [$name, $type]) {
            $catModels[$type][] = ExpenseCategory::create([
                'name' => $name, 'type' => $type, 'party_model' => null, 'is_active' => true,
            ]);
        }

        // Spread payments and receipts across the last 6 months.
        for ($m = 5; $m >= 0; $m--) {
            $base = Carbon::now()->subMonths($m);
            foreach (range(1, random_int(4, 7)) as $n) {
                $cat = $catModels['payment'][array_rand($catModels['payment'])];
                DailyTransaction::create([
                    'type' => 'payment',
                    'expense_category_id' => $cat->id,
                    'amount' => random_int(2, 25) * 500,
                    'date' => $base->copy()->addDays(random_int(0, 26))->toDateString(),
                    'payment_mode' => ['cash', 'bank', 'upi'][array_rand([0, 1, 2])],
                    'description' => $cat->name,
                    'created_by' => $owner->id,
                ]);
            }
            foreach (range(1, random_int(1, 3)) as $n) {
                $cat = $catModels['receipt'][array_rand($catModels['receipt'])];
                DailyTransaction::create([
                    'type' => 'receipt',
                    'expense_category_id' => $cat->id,
                    'amount' => random_int(2, 12) * 1000,
                    'date' => $base->copy()->addDays(random_int(0, 26))->toDateString(),
                    'payment_mode' => ['cash', 'bank', 'upi'][array_rand([0, 1, 2])],
                    'description' => $cat->name,
                    'created_by' => $owner->id,
                ]);
            }
        }
    }

    private function clientPortal(array $productIds): void
    {
        $problemType = TicketProblemType::first();

        foreach ([['Shree Engineering', 'client1@test.local'], ['Metro Fabricators', 'client2@test.local']] as [$name, $email]) {
            $client = ClientAccount::create([
                'name' => $name,
                'email' => $email,
                'phone' => '99' . random_int(10000000, 99999999),
                'password' => Hash::make('Client@123'),
                'is_active' => true,
            ]);

            for ($i = 0; $i < 2; $i++) {
                $machine = ClientMachine::create([
                    'client_account_id' => $client->id,
                    'product_id' => $productIds[array_rand($productIds)],
                    'serial_number' => 'SN-' . random_int(10000, 99999),
                    'installed_at' => Carbon::now()->subMonths(random_int(3, 18))->toDateString(),
                ]);

                if ($problemType && $i === 0) {
                    Ticket::create([
                        'client_machine_id' => $machine->id,
                        'client_account_id' => $client->id,
                        'problem_type_id' => $problemType->id,
                        'description' => 'Machine needs a service check.',
                        'status' => 'open',
                    ]);
                }
            }
        }
    }
}
