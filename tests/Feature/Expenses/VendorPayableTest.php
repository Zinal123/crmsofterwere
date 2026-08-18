<?php

namespace Tests\Feature\Expenses;

use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorBill;
use App\Models\VendorPayment;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VendorPayableTest extends TestCase
{
    use RefreshDatabase;

    private function vendor(): Vendor
    {
        return Vendor::create([
            'name' => 'Raytools India Pvt Ltd',
            'category' => 'Accessories',
            'is_active' => true,
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_vendor_payments_index_lists_payments_across_all_vendors_in_range(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $inRangeVendor = Vendor::create(['name' => 'In Range Vendor Co', 'category' => 'Accessories', 'is_active' => true]);
        $outOfRangeVendor = Vendor::create(['name' => 'Out Of Range Vendor Co', 'category' => 'Accessories', 'is_active' => true]);
        VendorPayment::create(['vendor_id' => $inRangeVendor->id, 'amount' => 5000, 'date' => '2026-07-10', 'payment_mode' => 'cash', 'created_by' => $owner->id]);
        VendorPayment::create(['vendor_id' => $outOfRangeVendor->id, 'amount' => 8000, 'date' => '2026-06-10', 'payment_mode' => 'cash', 'created_by' => $owner->id]);

        $response = $this->actingAs($owner)->get(route('vendor-payments.index', ['from' => '2026-07-01', 'to' => '2026-07-31']));

        $response->assertOk();
        $response->assertSee('In Range Vendor Co');
        $response->assertDontSee('Out Of Range Vendor Co');
    }

    public function test_vendor_payments_index_links_each_row_to_that_vendors_page(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $vendor = $this->vendor();
        VendorPayment::create(['vendor_id' => $vendor->id, 'amount' => 5000, 'date' => '2026-07-10', 'payment_mode' => 'cash', 'created_by' => $owner->id]);

        $response = $this->actingAs($owner)->get(route('vendor-payments.index', ['from' => '2026-07-01', 'to' => '2026-07-31']));

        $response->assertOk();
        $response->assertSee(route('admin.vendors.show', $vendor->id));
    }

    public function test_worker_cannot_view_the_vendor_payments_index(): void
    {
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);

        $response = $this->actingAs($worker)->get(route('vendor-payments.index'));

        $response->assertForbidden();
    }

    public function test_owner_can_view_a_vendors_payable_ledger(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $vendor = $this->vendor();

        $response = $this->actingAs($owner)->get(route('admin.vendors.show', $vendor->id));

        $response->assertOk();
        $response->assertSee('Raytools India Pvt Ltd');
    }

    public function test_owner_can_record_a_bill_against_a_vendor(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $vendor = $this->vendor();

        $response = $this->actingAs($owner)->post(route('admin.vendors.bills.store', $vendor->id), [
            'bill_number' => 'RT-2026-014',
            'amount' => 45000,
            'date' => now()->toDateString(),
            'description' => 'Ceramic nozzles, 50 units',
        ]);

        $response->assertRedirect(route('admin.vendors.show', $vendor->id));
        $this->assertDatabaseHas('vendor_bills', [
            'vendor_id' => $vendor->id,
            'bill_number' => 'RT-2026-014',
            'amount' => 45000,
        ]);
    }

    public function test_owner_can_record_a_payment_against_a_specific_bill(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $vendor = $this->vendor();
        $bill = VendorBill::create(['vendor_id' => $vendor->id, 'amount' => 45000, 'date' => now(), 'created_by' => $owner->id]);

        $response = $this->actingAs($owner)->post(route('admin.vendors.payments.store', $vendor->id), [
            'vendor_bill_id' => $bill->id,
            'amount' => 20000,
            'date' => now()->toDateString(),
            'payment_mode' => 'bank',
        ]);

        $response->assertRedirect(route('admin.vendors.show', $vendor->id));
        $this->assertDatabaseHas('vendor_payments', [
            'vendor_id' => $vendor->id,
            'vendor_bill_id' => $bill->id,
            'amount' => 20000,
        ]);
        $this->assertSame(25000.0, $bill->fresh()->balance());
    }

    public function test_owner_can_record_a_general_advance_payment_with_no_bill(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $vendor = $this->vendor();

        $response = $this->actingAs($owner)->post(route('admin.vendors.payments.store', $vendor->id), [
            'amount' => 10000,
            'date' => now()->toDateString(),
            'payment_mode' => 'cash',
            'description' => 'Advance for next order',
        ]);

        $response->assertRedirect(route('admin.vendors.show', $vendor->id));
        $this->assertDatabaseHas('vendor_payments', [
            'vendor_id' => $vendor->id,
            'vendor_bill_id' => null,
            'amount' => 10000,
        ]);
    }

    public function test_a_payment_against_a_specific_bill_cannot_exceed_its_remaining_balance(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $vendor = $this->vendor();
        $bill = VendorBill::create(['vendor_id' => $vendor->id, 'amount' => 5000, 'date' => now(), 'created_by' => $owner->id]);

        $response = $this->actingAs($owner)->postJson(route('admin.vendors.payments.store', $vendor->id), [
            'vendor_bill_id' => $bill->id,
            'amount' => 8000,
            'date' => now()->toDateString(),
            'payment_mode' => 'cash',
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseMissing('vendor_payments', ['vendor_bill_id' => $bill->id]);
    }

    public function test_vendors_outstanding_balance_sums_all_bills_minus_all_payments(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $vendor = $this->vendor();
        VendorBill::create(['vendor_id' => $vendor->id, 'amount' => 45000, 'date' => now(), 'created_by' => $owner->id]);
        VendorBill::create(['vendor_id' => $vendor->id, 'amount' => 15000, 'date' => now(), 'created_by' => $owner->id]);

        $this->actingAs($owner)->post(route('admin.vendors.payments.store', $vendor->id), [
            'amount' => 20000,
            'date' => now()->toDateString(),
            'payment_mode' => 'bank',
        ]);

        $response = $this->actingAs($owner)->get(route('admin.vendors.show', $vendor->id));

        $response->assertOk();
        // 45000 + 15000 billed - 20000 paid = 40000 outstanding
        $response->assertSee('40,000');
    }

    public function test_owner_can_update_a_bill(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $vendor = $this->vendor();
        $bill = VendorBill::create(['vendor_id' => $vendor->id, 'bill_number' => 'OLD-1', 'amount' => 45000, 'date' => now(), 'created_by' => $owner->id]);

        $response = $this->actingAs($owner)->put(route('admin.vendors.bills.update', [$vendor->id, $bill->id]), [
            'bill_number' => 'NEW-2',
            'amount' => 48000,
            'date' => now()->toDateString(),
            'description' => 'Corrected amount',
        ]);

        $response->assertRedirect(route('admin.vendors.show', $vendor->id));
        $this->assertDatabaseHas('vendor_bills', ['id' => $bill->id, 'bill_number' => 'NEW-2', 'amount' => 48000]);
    }

    public function test_owner_can_delete_a_bill(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $vendor = $this->vendor();
        $bill = VendorBill::create(['vendor_id' => $vendor->id, 'amount' => 45000, 'date' => now(), 'created_by' => $owner->id]);

        $response = $this->actingAs($owner)->delete(route('admin.vendors.bills.destroy', [$vendor->id, $bill->id]));

        $response->assertRedirect(route('admin.vendors.show', $vendor->id));
        $this->assertDatabaseMissing('vendor_bills', ['id' => $bill->id]);
    }

    public function test_owner_can_update_a_payment(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $vendor = $this->vendor();
        $payment = VendorPayment::create(['vendor_id' => $vendor->id, 'amount' => 10000, 'date' => now(), 'payment_mode' => 'cash', 'created_by' => $owner->id]);

        $response = $this->actingAs($owner)->put(route('admin.vendors.payments.update', [$vendor->id, $payment->id]), [
            'amount' => 12000,
            'date' => now()->toDateString(),
            'payment_mode' => 'bank',
            'description' => 'Corrected',
        ]);

        $response->assertRedirect(route('admin.vendors.show', $vendor->id));
        $this->assertDatabaseHas('vendor_payments', ['id' => $payment->id, 'amount' => 12000, 'payment_mode' => 'bank']);
    }

    public function test_owner_can_delete_a_payment(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $vendor = $this->vendor();
        $payment = VendorPayment::create(['vendor_id' => $vendor->id, 'amount' => 10000, 'date' => now(), 'payment_mode' => 'cash', 'created_by' => $owner->id]);

        $response = $this->actingAs($owner)->delete(route('admin.vendors.payments.destroy', [$vendor->id, $payment->id]));

        $response->assertRedirect(route('admin.vendors.show', $vendor->id));
        $this->assertDatabaseMissing('vendor_payments', ['id' => $payment->id]);
    }

    public function test_vendor_show_page_has_edit_and_delete_triggers_for_bills_and_payments(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $vendor = $this->vendor();
        $bill = VendorBill::create(['vendor_id' => $vendor->id, 'amount' => 45000, 'date' => now(), 'created_by' => $owner->id]);
        $payment = VendorPayment::create(['vendor_id' => $vendor->id, 'amount' => 10000, 'date' => now(), 'payment_mode' => 'cash', 'created_by' => $owner->id]);

        $response = $this->actingAs($owner)->get(route('admin.vendors.show', $vendor->id));

        $response->assertOk();
        $response->assertSee('data-bs-target="#editBill-' . $bill->id . '"', false);
        $response->assertSee(route('admin.vendors.bills.destroy', [$vendor->id, $bill->id]), false);
        $response->assertSee('data-bs-target="#editPayment-' . $payment->id . '"', false);
        $response->assertSee(route('admin.vendors.payments.destroy', [$vendor->id, $payment->id]), false);
    }

    public function test_worker_cannot_view_vendor_payable_ledger(): void
    {
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);
        $vendor = $this->vendor();

        $response = $this->actingAs($worker)->get(route('admin.vendors.show', $vendor->id));

        $response->assertForbidden();
    }
}
