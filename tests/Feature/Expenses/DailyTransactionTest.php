<?php

namespace Tests\Feature\Expenses;

use App\Models\Employee;
use App\Models\ExpenseCategory;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorBill;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DailyTransactionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_owner_can_view_the_daily_expenses_page(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('Owner');

        $response = $this->actingAs($owner)->get(route('expenses.index'));

        $response->assertOk();
    }

    public function test_worker_cannot_view_the_daily_expenses_page(): void
    {
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);

        $response = $this->actingAs($worker)->get(route('expenses.index'));

        $response->assertForbidden();
    }

    public function test_a_standalone_expense_with_no_party_is_recorded_directly(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $category = ExpenseCategory::create(['name' => 'Electricity', 'type' => 'payment', 'party_model' => null]);

        $response = $this->actingAs($owner)->post(route('expenses.store'), [
            'type' => 'payment',
            'expense_category_id' => $category->id,
            'amount' => 3500,
            'date' => now()->toDateString(),
            'payment_mode' => 'bank',
            'description' => 'August electricity bill',
        ]);

        $response->assertRedirect(route('expenses.index'));
        $this->assertDatabaseHas('daily_transactions', [
            'type' => 'payment',
            'expense_category_id' => $category->id,
            'amount' => 3500,
            'party_type' => null,
            'linked_type' => null,
        ]);
    }

    public function test_a_worker_wages_entry_creates_a_real_salary_payment_and_links_to_it(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $employee = Employee::factory()->create();
        $category = ExpenseCategory::create(['name' => 'Worker Wages', 'type' => 'payment', 'party_model' => 'employee']);

        $response = $this->actingAs($owner)->post(route('expenses.store'), [
            'type' => 'payment',
            'expense_category_id' => $category->id,
            'employee_id' => $employee->id,
            'amount' => 1500,
            'date' => now()->toDateString(),
            'payment_mode' => 'cash',
        ]);

        $response->assertRedirect(route('expenses.index'));
        $this->assertDatabaseHas('salary_payments', [
            'employee_id' => $employee->id,
            'amount' => 1500,
            'paid_by' => $owner->id,
        ]);
        $transaction = \App\Models\DailyTransaction::first();
        $this->assertSame('employee', $transaction->party_type);
        $this->assertSame($employee->id, $transaction->party_id);
        $this->assertSame('salary_payment', $transaction->linked_type);
        $this->assertNotNull($transaction->linked_id);

        // Shows up on the existing Payroll page too - it's a real payment, not a copy.
        $payrollResponse = $this->actingAs($owner)->get(route('employees.payroll', $employee->id));
        $payrollResponse->assertOk();
    }

    public function test_a_worker_wages_entry_without_an_employee_is_rejected(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $category = ExpenseCategory::create(['name' => 'Worker Wages', 'type' => 'payment', 'party_model' => 'employee']);

        $response = $this->actingAs($owner)->post(route('expenses.store'), [
            'type' => 'payment',
            'expense_category_id' => $category->id,
            'amount' => 1500,
            'date' => now()->toDateString(),
            'payment_mode' => 'cash',
        ]);

        $response->assertSessionHasErrors('employee_id');
        $this->assertDatabaseMissing('daily_transactions', ['expense_category_id' => $category->id]);
    }

    public function test_a_vendor_payment_entry_creates_a_real_vendor_payment_and_links_to_it(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $vendor = Vendor::create(['name' => 'Raytools India Pvt Ltd', 'category' => 'Accessories', 'is_active' => true]);
        $bill = VendorBill::create(['vendor_id' => $vendor->id, 'amount' => 10000, 'date' => now(), 'created_by' => $owner->id]);
        $category = ExpenseCategory::create(['name' => 'Vendor Payment', 'type' => 'payment', 'party_model' => 'vendor']);

        $response = $this->actingAs($owner)->post(route('expenses.store'), [
            'type' => 'payment',
            'expense_category_id' => $category->id,
            'vendor_id' => $vendor->id,
            'vendor_bill_id' => $bill->id,
            'amount' => 4000,
            'date' => now()->toDateString(),
            'payment_mode' => 'bank',
        ]);

        $response->assertRedirect(route('expenses.index'));
        $this->assertDatabaseHas('vendor_payments', ['vendor_id' => $vendor->id, 'vendor_bill_id' => $bill->id, 'amount' => 4000]);
        $transaction = \App\Models\DailyTransaction::first();
        $this->assertSame('vendor', $transaction->party_type);
        $this->assertSame('vendor_payment', $transaction->linked_type);
        $this->assertSame(6000.0, $bill->fresh()->balance());
    }

    public function test_a_receipt_entry_records_money_in(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $category = ExpenseCategory::create(['name' => 'Other Income', 'type' => 'receipt', 'party_model' => null]);

        $response = $this->actingAs($owner)->post(route('expenses.store'), [
            'type' => 'receipt',
            'expense_category_id' => $category->id,
            'amount' => 2000,
            'date' => now()->toDateString(),
            'payment_mode' => 'cash',
            'description' => 'Sold scrap metal',
        ]);

        $response->assertRedirect(route('expenses.index'));
        $this->assertDatabaseHas('daily_transactions', ['type' => 'receipt', 'amount' => 2000]);
    }

    public function test_a_receipt_photo_can_be_attached_but_is_not_required(): void
    {
        Storage::fake('public');
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $category = ExpenseCategory::create(['name' => 'Diesel / Fuel', 'type' => 'payment', 'party_model' => null]);

        $withoutPhoto = $this->actingAs($owner)->post(route('expenses.store'), [
            'type' => 'payment',
            'expense_category_id' => $category->id,
            'amount' => 1200,
            'date' => now()->toDateString(),
            'payment_mode' => 'cash',
        ]);
        $withoutPhoto->assertRedirect(route('expenses.index'));

        $withPhoto = $this->actingAs($owner)->post(route('expenses.store'), [
            'type' => 'payment',
            'expense_category_id' => $category->id,
            'amount' => 1300,
            'date' => now()->toDateString(),
            'payment_mode' => 'cash',
            'receipt_photo' => UploadedFile::fake()->image('diesel-receipt.jpg'),
        ]);
        $withPhoto->assertRedirect(route('expenses.index'));
        $transaction = \App\Models\DailyTransaction::where('amount', 1300)->first();
        $this->assertNotNull($transaction->receipt_photo);
    }
}
