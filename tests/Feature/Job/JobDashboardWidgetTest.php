<?php

namespace Tests\Feature\Job;

use App\Models\Job;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobDashboardWidgetTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_job_stats_without_breaking_existing_widgets(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        Job::factory()->create(['status' => 'pending_approval']);
        Job::factory()->create(['status' => 'completed', 'completed_at' => now()]);

        $response = $this->actingAs($owner)->get(route('root'));

        $response->assertOk();
        $response->assertViewHas('jobStats');
        // Existing dashboard keys must still be present — preserved behavior.
        $response->assertViewHas('totalRevenue');
        $response->assertViewHas('totalInvoices');
    }
}
