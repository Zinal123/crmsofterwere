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

    public function test_add_transaction_modal_uses_the_same_wording_as_the_table_badges(): void
    {
        // Table badges were reworded to "Money Out"/"Money In" but the
        // modal's own radio labels were left saying "Send Payment"/"Receive
        // Payment" - same page, two different phrasings for the same thing.
        $owner = User::factory()->create();
        $owner->assignRole('Owner');

        $response = $this->actingAs($owner)->get(route('expenses.index'));

        $response->assertOk();
        $response->assertDontSee('Send Payment');
        $response->assertDontSee('Receive Payment');
    }

    public function test_worker_cannot_view_the_daily_expenses_page(): void
    {
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);

        $response = $this->actingAs($worker)->get(route('expenses.index'));

        $response->assertForbidden();
    }

    public function test_daily_expenses_page_filters_by_date_range_and_type_when_provided(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $payment = ExpenseCategory::create(['name' => 'Diesel', 'type' => 'payment', 'party_model' => null]);
        $receipt = ExpenseCategory::create(['name' => 'Misc Income', 'type' => 'receipt', 'party_model' => null]);

        \App\Models\DailyTransaction::create(['type' => 'payment', 'expense_category_id' => $payment->id, 'amount' => 3000, 'date' => '2026-07-10', 'payment_mode' => 'cash', 'created_by' => $owner->id, 'description' => 'In-range payment']);
        \App\Models\DailyTransaction::create(['type' => 'receipt', 'expense_category_id' => $receipt->id, 'amount' => 5000, 'date' => '2026-07-10', 'payment_mode' => 'cash', 'created_by' => $owner->id, 'description' => 'In-range receipt']);
        \App\Models\DailyTransaction::create(['type' => 'payment', 'expense_category_id' => $payment->id, 'amount' => 9999, 'date' => '2026-06-10', 'payment_mode' => 'cash', 'created_by' => $owner->id, 'description' => 'Out-of-range payment']);

        $response = $this->actingAs($owner)->get(route('expenses.index', ['from' => '2026-07-01', 'to' => '2026-07-31', 'type' => 'payment']));

        $response->assertOk();
        $response->assertSee('In-range payment');
        $response->assertDontSee('In-range receipt');
        $response->assertDontSee('Out-of-range payment');
    }

    public function test_daily_expenses_page_can_exclude_linked_mirror_rows_to_match_the_pl_total(): void
    {
        // AccountingService excludes linked_type-mirror rows from Operating
        // Expenses to avoid double-counting a wage/vendor payment that's
        // already counted from payroll/vendor records. A drill-down link
        // from that report line has to exclude the same rows, or the list
        // it lands on won't add up to the number that was clicked.
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $wages = ExpenseCategory::create(['name' => 'Worker Wages', 'type' => 'payment', 'party_model' => 'employee']);
        \App\Models\DailyTransaction::create(['type' => 'payment', 'expense_category_id' => $wages->id, 'amount' => 20000, 'date' => '2026-07-10', 'payment_mode' => 'cash', 'created_by' => $owner->id, 'linked_type' => 'salary_payment', 'linked_id' => 1, 'description' => 'Payroll mirror row']);

        $response = $this->actingAs($owner)->get(route('expenses.index', ['from' => '2026-07-01', 'to' => '2026-07-31', 'type' => 'payment', 'exclude_mirrors' => '1']));

        $response->assertOk();
        $response->assertDontSee('Payroll mirror row');
    }

    public function test_daily_expenses_page_shows_everything_when_no_filter_given(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $category = ExpenseCategory::create(['name' => 'Diesel', 'type' => 'payment', 'party_model' => null]);
        \App\Models\DailyTransaction::create(['type' => 'payment', 'expense_category_id' => $category->id, 'amount' => 3000, 'date' => '2020-01-01', 'payment_mode' => 'cash', 'created_by' => $owner->id, 'description' => 'Ancient transaction']);

        $response = $this->actingAs($owner)->get(route('expenses.index'));

        $response->assertOk();
        $response->assertSee('Ancient transaction');
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

    public function test_owner_can_delete_a_standalone_transaction(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $category = ExpenseCategory::create(['name' => 'Diesel / Fuel', 'type' => 'payment', 'party_model' => null]);
        $transaction = app(\App\Services\Expenses\DailyTransactionService::class)->create($category, ['amount' => 900, 'date' => now()->toDateString(), 'payment_mode' => 'cash'], $owner);

        $response = $this->actingAs($owner)->delete(route('expenses.destroy', $transaction->id));

        $response->assertRedirect(route('expenses.index'));
        $this->assertDatabaseMissing('daily_transactions', ['id' => $transaction->id]);
    }

    public function test_deleting_a_worker_wages_transaction_also_deletes_the_real_salary_payment(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $employee = Employee::factory()->create();
        $category = ExpenseCategory::create(['name' => 'Worker Wages', 'type' => 'payment', 'party_model' => 'employee']);
        $transaction = app(\App\Services\Expenses\DailyTransactionService::class)->create($category, ['amount' => 500, 'date' => now()->toDateString(), 'payment_mode' => 'cash', 'employee_id' => $employee->id], $owner);
        $salaryPaymentId = $transaction->linked_id;

        $this->actingAs($owner)->delete(route('expenses.destroy', $transaction->id));

        $this->assertDatabaseMissing('daily_transactions', ['id' => $transaction->id]);
        $this->assertDatabaseMissing('salary_payments', ['id' => $salaryPaymentId]);
    }

    public function test_worker_cannot_delete_a_transaction(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);
        $category = ExpenseCategory::create(['name' => 'Diesel / Fuel', 'type' => 'payment', 'party_model' => null]);
        $transaction = app(\App\Services\Expenses\DailyTransactionService::class)->create($category, ['amount' => 900, 'date' => now()->toDateString(), 'payment_mode' => 'cash'], $owner);

        $response = $this->actingAs($worker)->delete(route('expenses.destroy', $transaction->id));

        $response->assertForbidden();
        $this->assertDatabaseHas('daily_transactions', ['id' => $transaction->id]);
    }

    public function test_expenses_page_has_a_delete_trigger_per_transaction(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $category = ExpenseCategory::create(['name' => 'Diesel / Fuel', 'type' => 'payment', 'party_model' => null]);
        $transaction = app(\App\Services\Expenses\DailyTransactionService::class)->create($category, ['amount' => 900, 'date' => now()->toDateString(), 'payment_mode' => 'cash'], $owner);

        $response = $this->actingAs($owner)->get(route('expenses.index'));

        $response->assertOk();
        $response->assertSee(route('expenses.destroy', $transaction->id), false);
    }
}
