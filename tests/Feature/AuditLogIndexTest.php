<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vendor;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogIndexTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_owner_sees_audit_entries_across_multiple_record_types(): void
    {
        // "created" events don't capture a field-level diff (no name/value
        // to show) - the log identifies a row by type + record id + action,
        // not the record's own display name.
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $vendor = Vendor::create(['name' => 'Global Log Vendor', 'category' => 'Raw Material']);

        $response = $this->actingAs($owner)->get(route('admin.audit-logs.index'));

        $response->assertOk();
        $response->assertSee('Vendor');
        $response->assertSee('created');
        $response->assertViewHas('logs', function ($logs) use ($vendor) {
            return $logs->contains(fn ($log) => $log->auditable_type === 'vendor' && $log->auditable_id === $vendor->id);
        });
    }

    public function test_index_only_shows_types_the_user_has_view_audit_access_to(): void
    {
        // A user with only products.view-audit must not see a vendor's
        // audit entries, and the type filter dropdown must not even offer
        // "Vendor" as an option.
        $user = User::factory()->create();
        $user->syncRoles([]);
        $user->givePermissionTo(['dashboard.view', 'products.view-audit']);
        Vendor::create(['name' => 'Hidden Vendor', 'category' => 'Raw Material']);

        $response = $this->actingAs($user)->get(route('admin.audit-logs.index'));

        $response->assertOk();
        $response->assertDontSee('Hidden Vendor');
        $response->assertDontSee('option value="vendor"', false);
    }

    public function test_index_shows_an_empty_state_for_a_user_with_no_audit_access_at_all(): void
    {
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);

        $response = $this->actingAs($worker)->get(route('admin.audit-logs.index'));

        $response->assertOk();
        $response->assertSee("don't have audit access");
    }

    public function test_can_filter_the_index_to_a_single_record_type(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $vendor = Vendor::create(['name' => 'Filtered Vendor', 'category' => 'Raw Material']);
        $category = \App\Models\ExpenseCategory::create(['name' => 'Filtered Category', 'type' => 'payment', 'party_model' => null]);

        $response = $this->actingAs($owner)->get(route('admin.audit-logs.index', ['type' => 'vendor']));

        $response->assertOk();
        $response->assertViewHas('logs', function ($logs) use ($vendor, $category) {
            return $logs->contains(fn ($log) => $log->auditable_type === 'vendor' && $log->auditable_id === $vendor->id)
                && ! $logs->contains(fn ($log) => $log->auditable_type === 'expense_category' && $log->auditable_id === $category->id);
        });
    }

    public function test_sidebar_shows_audit_log_link_only_for_a_user_with_some_audit_access(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);

        $this->actingAs($owner)->get(route('root'))->assertSee(route('admin.audit-logs.index'), false);
        $this->actingAs($worker)->get(route('jobs.index'))->assertDontSee(route('admin.audit-logs.index'), false);
    }
}
