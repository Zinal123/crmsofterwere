<?php

namespace Tests\Feature;

use App\Models\Quation;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuotationAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_a_quotation_logs_a_created_row(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $quotation = Quation::create(['clientname' => 'Test Client']);

        $response = $this->actingAs($owner)->get(route('audit-logs.for-record', ['type' => 'quotation', 'id' => $quotation->id]));

        $response->assertOk();
        $response->assertSee('created');
    }

    public function test_quotation_list_has_a_history_trigger_per_row(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $quotation = Quation::create(['clientname' => 'List Row Client']);

        $response = $this->actingAs($owner)->get(route('listqutation'));

        $response->assertOk();
        $response->assertSee('data-audit-id="' . $quotation->id . '"', false);
    }
}
