<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\Job;
use App\Models\Machine;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavigationConsistencyTest extends TestCase
{
    use RefreshDatabase;

    private function owner(): User
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');

        return $owner;
    }

    // --- Fix 1: back link present on the 15 previously-exit-less pages ---
    // (representative sample across every affected domain, not all 15 -
    // the fix is the same reusable <x-ui.back-link> component on each)

    public function test_add_employee_page_has_a_back_link(): void
    {
        $response = $this->actingAs($this->owner())->get(route('employees.create'));

        $response->assertOk();
        $response->assertSee(route('employees.index'), false);
        $response->assertSee('Back to Employees');
    }

    public function test_edit_employee_page_has_a_back_link(): void
    {
        $owner = $this->owner();
        $employee = Employee::factory()->create();

        $response = $this->actingAs($owner)->get(route('employees.edit', $employee->id));

        $response->assertOk();
        $response->assertSee(route('employees.index'), false);
        $response->assertSee('Back to Employees');
    }

    public function test_create_job_page_has_a_back_link(): void
    {
        $response = $this->actingAs($this->owner())->get(route('jobs.create'));

        $response->assertOk();
        $response->assertSee(route('jobs.index'), false);
        $response->assertSee('Back to Jobs');
    }

    public function test_job_detail_page_has_a_back_link(): void
    {
        $owner = $this->owner();
        $job = Job::factory()->create(['created_by' => $owner->id, 'assigned_to' => $owner->id]);

        $response = $this->actingAs($owner)->get(route('jobs.show', $job->id));

        $response->assertOk();
        $response->assertSee(route('jobs.index'), false);
        $response->assertSee('Back to Jobs');
    }

    public function test_mark_attendance_page_has_a_back_link(): void
    {
        $response = $this->actingAs($this->owner())->get(route('attendance.mark'));

        $response->assertOk();
        $response->assertSee(route('root'), false);
        $response->assertSee('Back to Dashboard');
    }

    public function test_attendance_register_page_has_a_back_link(): void
    {
        $owner = $this->owner();
        $employee = Employee::factory()->create();

        $response = $this->actingAs($owner)->get(route('attendance.register', $employee->id));

        $response->assertOk();
        $response->assertSee(route('employees.index'), false);
        $response->assertSee('Back to Employees');
    }

    public function test_payroll_page_has_a_back_link(): void
    {
        $owner = $this->owner();
        $employee = Employee::factory()->create();

        $response = $this->actingAs($owner)->get(route('employees.payroll', $employee->id));

        $response->assertOk();
        $response->assertSee(route('employees.index'), false);
        $response->assertSee('Back to Employees');
    }

    public function test_machines_page_has_a_back_link(): void
    {
        $response = $this->actingAs($this->owner())->get(route('machines.index'));

        $response->assertOk();
        $response->assertSee(route('root'), false);
        $response->assertSee('Back to Dashboard');
    }

    public function test_fiber_quotation_form_has_a_back_link(): void
    {
        $owner = $this->owner();
        $product = Product::factory()->create();

        $response = $this->actingAs($owner)->get(route('generatequtation', $product->id));

        $response->assertOk();
        $response->assertSee(route('listqutation'), false);
        $response->assertSee('Back to Quotations');
    }

    public function test_co2_quotation_form_has_a_back_link(): void
    {
        $owner = $this->owner();
        $product = Product::factory()->create();

        $response = $this->actingAs($owner)->get(route('co2quation', $product->id));

        $response->assertOk();
        $response->assertSee(route('listqutation'), false);
        $response->assertSee('Back to Quotations');
    }

    public function test_create_invoice_page_has_a_back_link(): void
    {
        $response = $this->actingAs($this->owner())->get(route('invoice.create'));

        $response->assertOk();
        $response->assertSee(route('invoice'), false);
        $response->assertSee('Back to Invoices');
    }

    public function test_invoice_details_page_has_a_print_hidden_back_link(): void
    {
        $owner = $this->owner();
        $invoice = Invoice::factory()->create(['amount' => 100000, 'amountwithtax' => 118000]);
        Customer::factory()->create(['invoice_id' => $invoice->id, 'state' => 'Gujarat']);

        $response = $this->actingAs($owner)->get(route('invoice.details', $invoice->id));

        $response->assertOk();
        $response->assertSee(route('invoice'), false);
        $response->assertSee('Back to Invoices');
        // Must not appear in a printed/exported copy of the invoice.
        $response->assertSee('d-print-none', false);
    }

    public function test_standerconfig_page_has_a_back_link(): void
    {
        $owner = $this->owner();
        $product = Product::factory()->create();

        $response = $this->actingAs($owner)->get(route('standerconfig', $product->id));

        $response->assertOk();
        $response->assertSee(route('product'), false);
        $response->assertSee('Back to Products');
    }

    // --- Fix 2: breadcrumb's first item links to the dashboard, not a dead javascript: link ---

    public function test_breadcrumb_first_item_links_to_the_dashboard_not_a_dead_link(): void
    {
        $response = $this->actingAs($this->owner())->get(route('product'));

        $response->assertOk();
        $response->assertDontSee('javascript: void(0);', false);
        $response->assertSee('href="' . route('root') . '"', false);
    }

    // --- Fix 3: audit-trail label standardized to "History" everywhere ---

    public function test_employee_edit_page_uses_history_label_not_audit_trail(): void
    {
        $owner = $this->owner();
        $employee = Employee::factory()->create();

        $response = $this->actingAs($owner)->get(route('employees.edit', $employee->id));

        $response->assertOk();
        $response->assertSee('History');
        $response->assertDontSee('Audit Trail');
    }

    public function test_invoice_details_page_uses_history_label_not_audit_trail(): void
    {
        $owner = $this->owner();
        $invoice = Invoice::factory()->create(['amount' => 100000, 'amountwithtax' => 118000]);
        Customer::factory()->create(['invoice_id' => $invoice->id, 'state' => 'Gujarat']);

        $response = $this->actingAs($owner)->get(route('invoice.details', $invoice->id));

        $response->assertOk();
        $response->assertSee('History');
        $response->assertDontSee('Audit Trail');
    }

    public function test_job_show_page_uses_history_label_not_audit_trail(): void
    {
        $owner = $this->owner();
        $job = Job::factory()->create(['created_by' => $owner->id, 'assigned_to' => $owner->id]);

        $response = $this->actingAs($owner)->get(route('jobs.show', $job->id));

        $response->assertOk();
        $response->assertSee('History');
        $response->assertDontSee('Audit Trail');
    }

    // --- Fix 4: sidebar icons no longer collide on the generic dashboard icon ---

    public function test_sidebar_gives_product_invoice_payment_history_and_quotation_distinct_icons(): void
    {
        // Inventory management was merged into the Product page (no longer
        // a separate sidebar item), so ri-archive-line is no longer asserted
        // here - see ProductInventoryMergeTest for the merged page itself.
        $response = $this->actingAs($this->owner())->get(route('root'));

        $response->assertOk();
        $response->assertSee('ri-price-tag-3-line', false);
        $response->assertSee('ri-bill-line', false);
        $response->assertSee('ri-wallet-2-line', false);
        $response->assertSee('ri-file-list-3-line', false);
    }
}
